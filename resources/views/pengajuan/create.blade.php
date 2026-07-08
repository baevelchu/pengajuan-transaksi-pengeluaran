@extends('layouts.app')
@section('title', 'Buat Pengajuan')
@section('content')
<h4 class="mb-3">Buat Pengajuan Transaksi Pengeluaran</h4>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('pengajuan.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tanggal Pengajuan</label>
                    <input type="date" name="tanggal_pengajuan" class="form-control" value="{{ old('tanggal_pengajuan', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Pengaju</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoris as $k)
                            <option value="{{ $k }}" {{ old('kategori') === $k ? 'selected' : '' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Pilih "PO Produk" jika pengajuan berupa Purchase Order produk (langsung ke approval Direktur).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nilai Pengajuan (Rp)</label>
                    <input type="number" step="0.01" min="1" name="nilai" class="form-control" value="{{ old('nilai') }}" required>
                    <div class="form-text">
                        Nilai &le; Rp 5.000.000: cukup approval SPV. Nilai &gt; Rp 5.000.000: SPV -> Manager. Nilai &gt; Rp 10.000.000: SPV -> Manager -> Direktur.
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="form-control">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Lampiran Dokumen Pendukung</label>
                <input type="file" name="lampiran" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text">Format PDF / JPG / JPEG / PNG, maksimal 5 MB. Wajib diisi saat mengajukan (boleh dikosongkan jika hanya menyimpan sebagai Draft).</div>
            </div>

            <button type="submit" name="mode" value="submit" class="btn btn-primary"><i class="bi bi-send"></i> Ajukan</button>
            <button type="submit" name="mode" value="draft" class="btn btn-outline-secondary" formnovalidate><i class="bi bi-save"></i> Simpan sebagai Draft</button>
            <a href="{{ route('pengajuan.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
