<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel `submissions`. Nama class dipertahankan `Pengajuan`
 * (bahasa Indonesia) agar konsisten dengan controller & view yang sudah ada,
 * namun tabel fisiknya bernama `submissions` sesuai desain database yang diminta.
 */
class Pengajuan extends Model
{
    protected $table = 'submissions';

    // Status Workflow (Kondisi/Ketentuan sesuai diagram)
    const STATUS_DRAFT = 'Draft';
    const STATUS_SUBMITTED = 'Submitted';
    const STATUS_WAITING_SPV = 'Waiting SPV Approval';
    const STATUS_WAITING_MANAGER = 'Waiting Manager Approval';
    const STATUS_WAITING_DIREKTUR = 'Waiting Director Approval';
    const STATUS_WAITING_FINANCE = 'Waiting Finance';
    const STATUS_PAID = 'Paid';
    const STATUS_REJECTED = 'Rejected';

    const BATAS_NILAI_SPV = 5000000;     // > 5jt masuk approval Manager
    const BATAS_NILAI_MANAGER = 10000000; // > 10jt lanjut ke Direktur

    protected $fillable = [
        'nomor_pengajuan', 'tanggal_pengajuan', 'user_id', 'category_id', 'kategori', 'nilai',
        'deskripsi', 'lampiran_path', 'lampiran_original_name', 'status',
        'is_po_produk', 'requires_direktur', 'rejected_reason', 'paid_at',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'nilai' => 'decimal:2',
        'is_po_produk' => 'boolean',
        'requires_direktur' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function approvalLogs()
    {
        return $this->hasMany(Approval::class, 'submission_id')->latest();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'submission_id');
    }

    /**
     * Accessor: mengembalikan nama kategori (string) dari relasi `category`,
     * supaya kode & view lama yang memakai $pengajuan->kategori tetap jalan.
     */
    public function getKategoriAttribute(): ?string
    {
        return $this->category?->name;
    }

    /**
     * Mutator: menerima nama kategori (string, dari form dropdown) lalu
     * mencari/membuat baris Category yang sesuai dan menyimpan category_id-nya.
     */
    public function setKategoriAttribute(string $value): void
    {
        $category = Category::firstOrCreate(['name' => trim($value)]);
        $this->attributes['category_id'] = $category->id;
    }

    public static function generateNomor(): string
    {
        $today = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        $nomor = 'PGJ-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        // pastikan unik walau ada race condition sederhana
        while (static::where('nomor_pengajuan', $nomor)->exists()) {
            $count++;
            $nomor = 'PGJ-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        return $nomor;
    }

    /**
     * Menentukan status awal & flag routing setelah pengajuan disubmit,
     * berdasarkan aturan workflow (Kondisi 1 - Kondisi 3).
     */
    public function tentukanRuteAwal(): void
    {
        $isPoProduk = strtolower(trim((string) $this->kategori)) === 'po produk';
        $this->is_po_produk = $isPoProduk;

        if ($isPoProduk) {
            // Kondisi 1: Kategori PO Produk -> langsung ke Direktur
            $this->requires_direktur = true;
            $this->status = self::STATUS_WAITING_DIREKTUR;
            return;
        }

        if ($this->nilai > self::BATAS_NILAI_MANAGER) {
            // Kondisi 3: nilai > 10jt -> SPV tidak dilibatkan sama sekali,
            // langsung ke Manager. Setelah Manager approve, baru diteruskan ke Direktur.
            $this->requires_direktur = true;
            $this->status = self::STATUS_WAITING_MANAGER;
            return;
        }

        // Kondisi 2: bukan PO Produk, nilai <= 10jt -> Staff -> SPV (selalu jadi titik awal).
        // Jika nilai > 5jt, setelah SPV approve akan diteruskan ke Manager (lihat routeAfterApproval).
        // Jika nilai <= 5jt, SPV menjadi approval terakhir.
        $this->requires_direktur = false;
        $this->status = self::STATUS_WAITING_SPV;
    }

    public function budget()
    {
        return Budget::where('category_id', $this->category_id)->first();
    }

    public function cekBudgetCukup(): bool
    {
        $budget = $this->budget();
        if (! $budget) {
            // Tidak ada data budget kategori -> anggap tidak cukup demi keamanan
            return false;
        }

        return $budget->isCukup((float) $this->nilai);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_SUBMITTED => 'info',
            self::STATUS_WAITING_SPV, self::STATUS_WAITING_MANAGER, self::STATUS_WAITING_DIREKTUR, self::STATUS_WAITING_FINANCE => 'warning',
            self::STATUS_PAID => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }
}
