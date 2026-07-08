@extends('layouts.app')
@section('title', 'Proses Approval')
@section('content')
<h4 class="mb-3">Proses Approval - {{ strtoupper($role) }}</h4>

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
                            @else - @endif
                        </td>
                    </tr>
                    <tr><th>Status Saat Ini</th><td><span class="badge bg-{{ $pengajuan->statusBadgeClass() }}">{{ $pengajuan->status }}</span></td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Beri Keputusan</div>
            <div class="card-body">
                <form method="POST" action="{{ route('approval.decide', [$role, $pengajuan]) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Catatan Approval</label>
                        <textarea name="catatan" rows="3" class="form-control" placeholder="Opsional untuk approve, disarankan diisi untuk reject"></textarea>
                    </div>
                    <button type="submit" name="action" value="approve" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger"
                        onclick="return confirm('Yakin ingin menolak pengajuan ini?')">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>
                </form>
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

<a href="{{ route('approval.index', $role) }}" class="btn btn-outline-secondary mt-3">Kembali</a>
@endsection
