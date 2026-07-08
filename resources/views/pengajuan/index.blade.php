@extends('layouts.app')
@section('title', 'Riwayat Pengajuan')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="mb-1 fw-bold"><i class="bi bi-clock-history text-primary me-1"></i> Riwayat Pengajuan Saya</h5>
        <p class="text-muted small mb-0">Pantau status dan riwayat seluruh pengajuan transaksi Anda.</p>
    </div>
    <a href="{{ route('pengajuan.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Buat Pengajuan</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No. Pengajuan</th>
                        <th>Tanggal</th>
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
                            <td>{{ $p->tanggal_pengajuan->format('d-m-Y') }}</td>
                            <td>{{ $p->kategori }}</td>
                            <td class="text-end">Rp {{ number_format($p->nilai, 0, ',', '.') }}</td>
                            <td><span class="badge bg-{{ $p->statusBadgeClass() }} badge-status">{{ $p->status }}</span></td>
                            <td>
                                <a href="{{ route('pengajuan.show', $p) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                @if($p->status === 'Draft')
                                    <a href="{{ route('pengajuan.edit', $p) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('pengajuan.submit', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Ajukan pengajuan ini sekarang?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Ajukan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pengajuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pengajuans->links() }}
    </div>
</div>
@endsection
