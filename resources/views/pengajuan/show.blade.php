@extends('layouts.app')
@section('title', 'Detail Pengajuan')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Detail Pengajuan {{ $pengajuan->nomor_pengajuan }}</h4>
    <span class="badge bg-{{ $pengajuan->statusBadgeClass() }} fs-6">{{ $pengajuan->status }}</span>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th width="200">Nomor Pengajuan</th><td>{{ $pengajuan->nomor_pengajuan }}</td></tr>
                    <tr><th>Tanggal Pengajuan</th><td>{{ $pengajuan->tanggal_pengajuan->format('d-m-Y') }}</td></tr>
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
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @if($pengajuan->status === 'Rejected')
                        <tr><th>Alasan Ditolak</th><td class="text-danger">{{ $pengajuan->rejected_reason }}</td></tr>
                    @endif
                </table>
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

<a href="{{ route('pengajuan.index') }}" class="btn btn-outline-secondary mt-3">Kembali</a>
@if($pengajuan->status === 'Draft')
    <a href="{{ route('pengajuan.edit', $pengajuan) }}" class="btn btn-outline-primary mt-3">Edit Draft</a>
    <form action="{{ route('pengajuan.submit', $pengajuan) }}" method="POST" class="d-inline" onsubmit="return confirm('Ajukan pengajuan ini sekarang?')">
        @csrf
        <button type="submit" class="btn btn-primary mt-3">Ajukan Sekarang</button>
    </form>
@endif
@endsection
