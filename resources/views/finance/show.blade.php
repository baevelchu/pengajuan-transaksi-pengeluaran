@extends('layouts.app')
@section('title', 'Proses Pembayaran')
@section('content')
<h4 class="mb-3">Proses Pembayaran</h4>

<div class="row">
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th width="200">Nomor Pengajuan</th><td>{{ $pengajuan->nomor_pengajuan }}</td></tr>
                    <tr><th>Nama Pengaju</th><td>{{ $pengajuan->user->name }}</td></tr>
                    <tr><th>Kategori</th><td>{{ $pengajuan->kategori }}</td></tr>
                    <tr><th>Nilai Pengajuan</th><td>Rp {{ number_format($pengajuan->nilai, 0, ',', '.') }}</td></tr>
                    <tr><th>Deskripsi</th><td>{{ $pengajuan->deskripsi ?: '-' }}</td></tr>
                    <tr>
                        <th>Lampiran</th>
                        <td>
                            @if($pengajuan->lampiran_path)
                                <a href="{{ Storage::url($pengajuan->lampiran_path) }}" target="_blank">
                                    <i class="bi bi-paperclip"></i> {{ $pengajuan->lampiran_original_name }}
                                </a>
                            @else - @endif
                        </td>
                    </tr>
                    <tr><th>Status</th><td><span class="badge bg-{{ $pengajuan->statusBadgeClass() }}">{{ $pengajuan->status }}</span></td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p>Saldo Perusahaan saat ini: <strong>Rp {{ number_format($saldo->saldo, 0, ',', '.') }}</strong></p>
                @if($pengajuan->status === 'Waiting Finance')
                    <p class="text-muted small">
                        Sistem akan otomatis memeriksa kecukupan saldo. Jika saldo mencukupi, status akan berubah menjadi <strong>Paid</strong>
                        dan saldo akan dikurangi. Jika tidak mencukupi, pengajuan otomatis <strong>Ditolak</strong>.
                    </p>
                    <form method="POST" action="{{ route('finance.proses', $pengajuan) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Catatan (opsional)</label>
                            <textarea name="catatan" rows="2" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Proses pembayaran / cek saldo untuk pengajuan ini?')">
                            <i class="bi bi-check2-circle"></i> Cek Saldo &amp; Proses
                        </button>
                    </form>
                @else
                    <div class="alert alert-info mb-0">Pengajuan ini sudah diproses sebelumnya.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Riwayat Approval</div>
            <ul class="list-group list-group-flush">
                @forelse($pengajuan->approvalLogs as $log)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>{{ strtoupper($log->role) }}</strong>
                            <small class="text-muted">{{ $log->created_at->format('d-m-Y H:i') }}</small>
                        </div>
                        <div>{{ $log->user->name }} - <span class="fw-semibold">{{ $log->action }}</span></div>
                        @if($log->catatan)
                            <div class="small text-muted">Catatan: {{ $log->catatan }}</div>
                        @endif
                    </li>
                @empty
                    <li class="list-group-item text-muted">Belum ada aktivitas approval.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<a href="{{ route('finance.index') }}" class="btn btn-outline-secondary mt-3">Kembali</a>
@endsection
