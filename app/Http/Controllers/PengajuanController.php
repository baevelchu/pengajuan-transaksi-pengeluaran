<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengajuanController extends Controller
{
    public function index()
    {
        $pengajuans = Pengajuan::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('pengajuan.index', compact('pengajuans'));
    }

    public function create()
    {
        $kategoris = Category::orderBy('name')->pluck('name');

        return view('pengajuan.create', compact('kategoris'));
    }

    /**
     * Simpan pengajuan baru. Bisa disimpan sebagai Draft (belum masuk workflow approval)
     * atau langsung diajukan (submit) yang otomatis merutekan ke approval pertama.
     */
    public function store(Request $request)
    {
        $isDraft = $request->input('mode') === 'draft';

        $rules = [
            'tanggal_pengajuan' => ['required', 'date'],
            'kategori' => ['required', 'string', 'max:100'],
            'nilai' => ['required', 'numeric', 'min:1'],
            'deskripsi' => ['nullable', 'string'],
            'lampiran' => [$isDraft ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];

        $validated = $request->validate($rules, [
            'lampiran.mimes' => 'Lampiran harus berupa file PDF, JPG, JPEG, atau PNG.',
            'lampiran.max' => 'Ukuran lampiran maksimal 5 MB.',
            'lampiran.required' => 'Lampiran wajib diunggah saat mengajukan (tidak wajib jika disimpan sebagai draft).',
        ]);

        DB::beginTransaction();
        try {
            $path = null;
            $originalName = null;
            if ($request->hasFile('lampiran')) {
                $path = $request->file('lampiran')->store('lampiran-pengajuan', 'public');
                $originalName = $request->file('lampiran')->getClientOriginalName();
            }

            $pengajuan = new Pengajuan([
                'nomor_pengajuan' => Pengajuan::generateNomor(),
                'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
                'user_id' => Auth::id(),
                'kategori' => $validated['kategori'],
                'nilai' => $validated['nilai'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'lampiran_path' => $path,
                'lampiran_original_name' => $originalName,
                'status' => Pengajuan::STATUS_DRAFT,
            ]);

            if ($isDraft) {
                // Kondisi Draft: hanya disimpan, belum masuk workflow approval.
                $pengajuan->status = Pengajuan::STATUS_DRAFT;
            } else {
                $pengajuan->status = Pengajuan::STATUS_SUBMITTED;
                $pengajuan->tentukanRuteAwal();
            }

            $pengajuan->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan pengajuan: ' . $e->getMessage()]);
        }

        $message = $isDraft
            ? 'Pengajuan berhasil disimpan sebagai Draft dengan nomor ' . $pengajuan->nomor_pengajuan . '. Anda bisa mengeditnya dan mengajukan kapan saja.'
            : 'Pengajuan berhasil diajukan dengan nomor ' . $pengajuan->nomor_pengajuan;

        return redirect()->route('pengajuan.show', $pengajuan)->with('success', $message);
    }

    public function show(Pengajuan $pengajuan)
    {
        $this->authorizeView($pengajuan);

        $pengajuan->load(['approvalLogs.user', 'user']);

        return view('pengajuan.show', compact('pengajuan'));
    }

    public function edit(Pengajuan $pengajuan)
    {
        $this->authorizeView($pengajuan);
        $this->authorizeOwnerDraft($pengajuan);

        $kategoris = Category::orderBy('name')->pluck('name');

        return view('pengajuan.edit', compact('pengajuan', 'kategoris'));
    }

    public function update(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeView($pengajuan);
        $this->authorizeOwnerDraft($pengajuan);

        $isDraft = $request->input('mode') === 'draft';

        $rules = [
            'tanggal_pengajuan' => ['required', 'date'],
            'kategori' => ['required', 'string', 'max:100'],
            'nilai' => ['required', 'numeric', 'min:1'],
            'deskripsi' => ['nullable', 'string'],
            'lampiran' => [$isDraft ? 'nullable' : ($pengajuan->lampiran_path ? 'nullable' : 'required'), 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];

        $validated = $request->validate($rules, [
            'lampiran.mimes' => 'Lampiran harus berupa file PDF, JPG, JPEG, atau PNG.',
            'lampiran.max' => 'Ukuran lampiran maksimal 5 MB.',
            'lampiran.required' => 'Lampiran wajib diunggah saat mengajukan.',
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('lampiran')) {
                if ($pengajuan->lampiran_path) {
                    Storage::disk('public')->delete($pengajuan->lampiran_path);
                }
                $pengajuan->lampiran_path = $request->file('lampiran')->store('lampiran-pengajuan', 'public');
                $pengajuan->lampiran_original_name = $request->file('lampiran')->getClientOriginalName();
            }

            $pengajuan->tanggal_pengajuan = $validated['tanggal_pengajuan'];
            $pengajuan->kategori = $validated['kategori'];
            $pengajuan->nilai = $validated['nilai'];
            $pengajuan->deskripsi = $validated['deskripsi'] ?? null;

            if ($isDraft) {
                $pengajuan->status = Pengajuan::STATUS_DRAFT;
            } else {
                if (! $pengajuan->lampiran_path) {
                    DB::rollBack();

                    return back()->withInput()->withErrors(['lampiran' => 'Lampiran wajib diunggah sebelum mengajukan.']);
                }
                $pengajuan->status = Pengajuan::STATUS_SUBMITTED;
                $pengajuan->tentukanRuteAwal();
            }

            $pengajuan->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui pengajuan: ' . $e->getMessage()]);
        }

        $message = $isDraft
            ? 'Perubahan pada Draft ' . $pengajuan->nomor_pengajuan . ' berhasil disimpan.'
            : 'Pengajuan ' . $pengajuan->nomor_pengajuan . ' berhasil diajukan ke approval.';

        return redirect()->route('pengajuan.show', $pengajuan)->with('success', $message);
    }

    /**
     * Ajukan (submit) Draft yang sudah ada, tanpa melalui form edit,
     * asalkan lampiran sudah pernah diunggah sebelumnya.
     */
    public function submit(Pengajuan $pengajuan)
    {
        $this->authorizeView($pengajuan);
        $this->authorizeOwnerDraft($pengajuan);

        if (! $pengajuan->lampiran_path) {
            return back()->withErrors(['error' => 'Lampiran wajib diunggah sebelum mengajukan. Silakan edit pengajuan terlebih dahulu.']);
        }

        $pengajuan->status = Pengajuan::STATUS_SUBMITTED;
        $pengajuan->tentukanRuteAwal();
        $pengajuan->save();

        return redirect()->route('pengajuan.show', $pengajuan)
            ->with('success', 'Pengajuan ' . $pengajuan->nomor_pengajuan . ' berhasil diajukan ke approval.');
    }

    protected function authorizeView(Pengajuan $pengajuan): void
    {
        $user = Auth::user();

        // Staff hanya boleh lihat pengajuan miliknya sendiri.
        if ($user->isStaff() && $pengajuan->user_id !== $user->id) {
            abort(403);
        }
    }

    protected function authorizeOwnerDraft(Pengajuan $pengajuan): void
    {
        if ($pengajuan->status !== Pengajuan::STATUS_DRAFT) {
            abort(403, 'Hanya pengajuan berstatus Draft yang dapat diedit/diajukan lewat halaman ini.');
        }
    }
}
