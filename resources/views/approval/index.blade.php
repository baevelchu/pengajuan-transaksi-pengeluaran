@extends('layouts.app')
@section('title', 'Antrian Approval')
@section('content')
<div class="mb-4">
    <h5 class="mb-1 fw-bold"><i class="bi bi-check2-square text-primary me-1"></i> Antrian Approval — {{ strtoupper($role) }}</h5>
    <p class="text-muted small mb-0">Tinjau dan berikan keputusan pada pengajuan yang menunggu approval Anda.</p>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No. Pengajuan</th>
                        <th>Pengaju</th>
                        <th>Kategori</th>
                        <th class="text-end">Nilai</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        <tr>
                            <td>{{ $p->nomor_pengajuan }}</td>
                            <td>{{ $p->user->name }}</td>
                            <td>{{ $p->kategori }}</td>
                            <td class="text-end">Rp {{ number_format($p->nilai, 0, ',', '.') }}</td>
                            <td><span class="badge bg-{{ $p->statusBadgeClass() }} badge-status">{{ $p->status }}</span></td>
                            <td><a href="{{ route('approval.show', [$role, $p]) }}" class="btn btn-sm btn-primary">Proses</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada pengajuan yang menunggu approval Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pengajuans->links() }}
    </div>
</div>

<h6 class="mb-3 fw-bold text-muted"><i class="bi bi-clock-history"></i> Riwayat Keputusan Saya (Terakhir)</h6>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr><th>No. Pengajuan</th><th>Pengaju</th><th>Status Sekarang</th></tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $r)
                        <tr>
                            <td><a href="{{ route('pengajuan.show', $r) }}">{{ $r->nomor_pengajuan }}</a></td>
                            <td>{{ $r->user->name }}</td>
                            <td><span class="badge bg-{{ $r->statusBadgeClass() }}">{{ $r->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center">Belum ada riwayat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
