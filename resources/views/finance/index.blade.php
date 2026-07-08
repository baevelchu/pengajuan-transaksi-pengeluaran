@extends('layouts.app')
@section('title', 'Antrian Finance')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Antrian Finance</h4>
    <div class="card">
        <div class="card-body py-2 px-3">
            <span class="text-muted small">Saldo Perusahaan</span><br>
            <strong class="fs-5">Rp {{ number_format($saldo->saldo, 0, ',', '.') }}</strong>
        </div>
    </div>
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
                            <td><span class="badge bg-{{ $p->statusBadgeClass() }}">{{ $p->status }}</span></td>
                            <td><a href="{{ route('finance.show', $p) }}" class="btn btn-sm btn-primary">Proses</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada pengajuan yang menunggu proses Finance.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pengajuans->links() }}
    </div>
</div>

<h5 class="mb-3">Riwayat Transaksi</h5>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>No. Pengajuan</th><th>Pengaju</th><th class="text-end">Nilai</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($riwayat as $r)
                        <tr>
                            <td><a href="{{ route('pengajuan.show', $r) }}">{{ $r->nomor_pengajuan }}</a></td>
                            <td>{{ $r->user->name }}</td>
                            <td class="text-end">Rp {{ number_format($r->nilai, 0, ',', '.') }}</td>
                            <td><span class="badge bg-{{ $r->statusBadgeClass() }}">{{ $r->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center">Belum ada riwayat transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
