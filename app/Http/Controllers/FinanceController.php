<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\CompanyBalance;
use App\Models\Payment;
use App\Models\Pengajuan;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        $pengajuans = Pengajuan::with('user')
            ->where('status', Pengajuan::STATUS_WAITING_FINANCE)
            ->orderBy('created_at')
            ->paginate(10);

        $riwayat = Pengajuan::where('status', Pengajuan::STATUS_PAID)
            ->orWhere(function ($q) {
                $q->where('status', Pengajuan::STATUS_REJECTED)
                    ->whereHas('approvalLogs', fn ($sub) => $sub->whereHas('roleRef', fn ($r) => $r->where('name', 'finance')));
            })
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $saldo = CompanyBalance::current();

        return view('finance.index', compact('pengajuans', 'riwayat', 'saldo'));
    }

    public function show(Pengajuan $pengajuan)
    {
        $pengajuan->load(['user', 'approvalLogs.user']);
        $saldo = CompanyBalance::current();

        return view('finance.show', compact('pengajuan', 'saldo'));
    }

    /**
     * Kondisi 7: Finance mengecek saldo perusahaan.
     * Jika cukup -> proses pembayaran, status Paid, dan dicatat di tabel `payments`.
     * Jika tidak cukup -> Rejected (payment dicatat dengan status Failed).
     */
    public function proses(Request $request, Pengajuan $pengajuan)
    {
        if ($pengajuan->status !== Pengajuan::STATUS_WAITING_FINANCE) {
            return back()->withErrors(['error' => 'Pengajuan ini tidak sedang menunggu proses Finance.']);
        }

        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $saldo = CompanyBalance::current();
        $financeRoleId = Role::where('name', 'finance')->value('id');
        $saldoBefore = $saldo->saldo;

        DB::beginTransaction();
        try {
            if ($saldo->saldo >= $pengajuan->nilai) {
                // Saldo mencukupi -> proses pembayaran
                $saldo->saldo -= $pengajuan->nilai;
                $saldo->save();

                // Update pemakaian budget kategori
                $budget = $pengajuan->budget();
                if ($budget) {
                    $budget->used_budget += $pengajuan->nilai;
                    $budget->save();
                }

                $pengajuan->status = Pengajuan::STATUS_PAID;
                $pengajuan->paid_at = now();
                $pengajuan->save();

                Approval::create([
                    'submission_id' => $pengajuan->id,
                    'user_id' => Auth::id(),
                    'role_id' => $financeRoleId,
                    'action' => 'Proses Pembayaran',
                    'catatan' => $validated['catatan'] ?? null,
                ]);

                Payment::create([
                    'submission_id' => $pengajuan->id,
                    'processed_by' => Auth::id(),
                    'amount' => $pengajuan->nilai,
                    'saldo_before' => $saldoBefore,
                    'saldo_after' => $saldo->saldo,
                    'status' => 'Success',
                    'catatan' => $validated['catatan'] ?? null,
                    'paid_at' => now(),
                ]);

                $message = 'Pembayaran untuk pengajuan ' . $pengajuan->nomor_pengajuan . ' berhasil diproses.';
            } else {
                // Saldo tidak mencukupi -> Rejected
                $pengajuan->status = Pengajuan::STATUS_REJECTED;
                $pengajuan->rejected_reason = 'Saldo perusahaan tidak mencukupi untuk memproses pembayaran.';
                $pengajuan->save();

                Approval::create([
                    'submission_id' => $pengajuan->id,
                    'user_id' => Auth::id(),
                    'role_id' => $financeRoleId,
                    'action' => 'Reject (Saldo Tidak Cukup)',
                    'catatan' => $validated['catatan'] ?? null,
                ]);

                Payment::create([
                    'submission_id' => $pengajuan->id,
                    'processed_by' => Auth::id(),
                    'amount' => $pengajuan->nilai,
                    'saldo_before' => $saldoBefore,
                    'saldo_after' => $saldoBefore,
                    'status' => 'Failed',
                    'catatan' => $validated['catatan'] ?? 'Saldo tidak mencukupi',
                    'paid_at' => null,
                ]);

                $message = 'Pengajuan ' . $pengajuan->nomor_pengajuan . ' ditolak karena saldo perusahaan tidak mencukupi.';
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal memproses: ' . $e->getMessage()]);
        }

        return redirect()->route('finance.index')->with('success', $message);
    }
}
