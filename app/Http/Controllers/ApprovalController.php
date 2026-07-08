<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    /**
     * Daftar pengajuan yang perlu di-approve oleh role tertentu (spv/manager/direktur).
     */
    public function index(Request $request, string $role)
    {
        $this->guardRoleAccess($role);

        $statusMap = [
            'spv' => Pengajuan::STATUS_WAITING_SPV,
            'manager' => Pengajuan::STATUS_WAITING_MANAGER,
            'direktur' => Pengajuan::STATUS_WAITING_DIREKTUR,
        ];

        $pengajuans = Pengajuan::with('user')
            ->where('status', $statusMap[$role])
            ->orderBy('created_at')
            ->paginate(10);

        // Riwayat pengajuan yang sudah pernah diputuskan oleh user ini
        $riwayat = Pengajuan::whereHas('approvalLogs', function ($q) {
            $q->where('user_id', Auth::id());
        })->with('user')->orderByDesc('updated_at')->limit(10)->get();

        return view('approval.index', compact('pengajuans', 'role', 'riwayat'));
    }

    public function show(string $role, Pengajuan $pengajuan)
    {
        $this->guardRoleAccess($role);
        $pengajuan->load(['user', 'approvalLogs.user']);

        return view('approval.show', compact('pengajuan', 'role'));
    }

    public function decide(Request $request, string $role, Pengajuan $pengajuan)
    {
        $this->guardRoleAccess($role);

        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $expectedStatus = match ($role) {
            'spv' => Pengajuan::STATUS_WAITING_SPV,
            'manager' => Pengajuan::STATUS_WAITING_MANAGER,
            'direktur' => Pengajuan::STATUS_WAITING_DIREKTUR,
        };

        if ($pengajuan->status !== $expectedStatus) {
            return back()->withErrors(['error' => 'Pengajuan ini sudah tidak berada pada tahap approval ' . strtoupper($role) . '.']);
        }

        DB::beginTransaction();
        try {
            if ($validated['action'] === 'reject') {
                // Kondisi 5: salah satu approver reject -> Rejected
                $pengajuan->status = Pengajuan::STATUS_REJECTED;
                $pengajuan->rejected_reason = $validated['catatan'] ?? ('Ditolak oleh ' . strtoupper($role));
                $pengajuan->save();

                $this->logAction($pengajuan, $role, 'Reject', $validated['catatan'] ?? null);

                DB::commit();

                return redirect()->route('approval.index', ['role' => $role])
                    ->with('success', 'Pengajuan ' . $pengajuan->nomor_pengajuan . ' telah ditolak.');
            }

            // Action: approve
            // Kondisi 4: cek budget kategori sebelum melanjutkan approval di level SPV / Manager
            if (in_array($role, ['spv', 'manager'], true) && ! $pengajuan->cekBudgetCukup()) {
                $pengajuan->status = Pengajuan::STATUS_REJECTED;
                $pengajuan->rejected_reason = 'Budget tidak mencukupi pada kategori ' . $pengajuan->kategori;
                $pengajuan->save();

                $this->logAction($pengajuan, $role, 'Reject (Budget Tidak Cukup)', $validated['catatan'] ?? null);

                DB::commit();

                return redirect()->route('approval.index', ['role' => $role])
                    ->with('success', 'Pengajuan ' . $pengajuan->nomor_pengajuan . ' ditolak karena budget kategori tidak mencukupi.');
            }

            $this->logAction($pengajuan, $role, 'Approve', $validated['catatan'] ?? null);
            $this->routeAfterApproval($pengajuan, $role);
            $pengajuan->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal memproses approval: ' . $e->getMessage()]);
        }

        return redirect()->route('approval.index', ['role' => $role])
            ->with('success', 'Pengajuan ' . $pengajuan->nomor_pengajuan . ' berhasil diproses. Status sekarang: ' . $pengajuan->status);
    }

    /**
     * Menentukan status pengajuan berikutnya setelah disetujui pada level tertentu,
     * sesuai Kondisi 1 - Kondisi 3 & Kondisi 6.
     */
    protected function routeAfterApproval(Pengajuan $pengajuan, string $role): void
    {
        if ($role === 'spv') {
            // Kondisi 2: jika nilai > 5jt, setelah SPV approve diteruskan ke Manager.
            // Jika nilai <= 5jt, SPV adalah approval terakhir.
            $pengajuan->status = $pengajuan->nilai > Pengajuan::BATAS_NILAI_SPV
                ? Pengajuan::STATUS_WAITING_MANAGER
                : Pengajuan::STATUS_WAITING_FINANCE; // Kondisi 6
            return;
        }

        if ($role === 'manager') {
            // Kondisi 3: jika nilai > 10jt, setelah Manager approve diteruskan ke Direktur.
            // Jika tidak, Manager adalah approval terakhir.
            $pengajuan->status = $pengajuan->requires_direktur
                ? Pengajuan::STATUS_WAITING_DIREKTUR
                : Pengajuan::STATUS_WAITING_FINANCE; // Kondisi 6
            return;
        }

        // Direktur selalu menjadi approval terakhir (baik dari PO Produk maupun rantai SPV->Manager->Direktur)
        $pengajuan->status = Pengajuan::STATUS_WAITING_FINANCE; // Kondisi 6
    }

    protected function logAction(Pengajuan $pengajuan, string $role, string $action, ?string $catatan): void
    {
        Approval::create([
            'submission_id' => $pengajuan->id,
            'user_id' => Auth::id(),
            'role_id' => \App\Models\Role::where('name', $role)->value('id'),
            'action' => $action,
            'catatan' => $catatan,
        ]);
    }

    protected function guardRoleAccess(string $role): void
    {
        if (! in_array($role, ['spv', 'manager', 'direktur'], true)) {
            abort(404);
        }

        if (Auth::user()->role !== $role) {
            abort(403, 'Anda tidak memiliki akses ke antrian approval ini.');
        }
    }
}
