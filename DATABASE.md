# Desain Database — Sistem Pengajuan Transaksi Pengeluaran

Dokumen ini menjelaskan struktur tabel dan relasi antar tabel pada database
`pengajuan_transaksi`. Sesuai ketentuan, database minimal memiliki tabel:
`users`, `roles`, `submissions`, `approvals`, `categories`, `budgets`,
`payments`. Ditambah 1 tabel pendukung `company_balances` untuk menyimpan
saldo kas perusahaan yang dipakai Finance saat validasi saldo (Kondisi 7).

## 1. Daftar Tabel & Kolom

### `roles`
Master data role/peran pengguna.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| name | varchar, unique | staff / spv / manager / direktur / finance |
| label | varchar, nullable | Nama tampilan, mis. "Supervisor (SPV)" |
| description | text, nullable | Deskripsi wewenang role |
| timestamps | | |

### `users`
Akun pengguna sistem.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| name | varchar | |
| email | varchar, unique | |
| password | varchar (hashed) | |
| role_id | bigint, FK → `roles.id` | Role/peran user |
| remember_token | varchar, nullable | |
| timestamps | | |

### `categories`
Master data kategori transaksi pengeluaran (PO Produk, Operasional, dll).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| name | varchar, unique | Nama kategori, mis. "PO Produk" |
| description | text, nullable | |
| timestamps | | |

### `budgets`
Alokasi anggaran per kategori, dipakai untuk validasi Kondisi 4
(cek kecukupan budget).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| category_id | bigint, FK → `categories.id`, unique | 1 kategori = 1 baris budget |
| total_budget | decimal(18,2) | Total anggaran kategori tsb |
| used_budget | decimal(18,2) | Anggaran yang sudah terpakai (bertambah setiap pembayaran sukses) |
| timestamps | | |

### `submissions`
Data pengajuan transaksi pengeluaran (form utama).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| nomor_pengajuan | varchar, unique | Auto generate: `PGJ-YYYYMMDD-0001` |
| tanggal_pengajuan | date | |
| user_id | bigint, FK → `users.id` | Staff pengaju |
| category_id | bigint, FK → `categories.id` | Kategori pengajuan |
| nilai | decimal(18,2) | Nilai pengajuan |
| deskripsi | text, nullable | |
| lampiran_path | varchar, nullable | Path file di storage disk `public` |
| lampiran_original_name | varchar, nullable | Nama asli file upload |
| status | varchar | Draft/Submitted/Waiting SPV Approval/Waiting Manager Approval/Waiting Director Approval/Waiting Finance/Paid/Rejected |
| is_po_produk | boolean | Flag hasil evaluasi Kondisi 1 |
| requires_direktur | boolean | Flag apakah harus lanjut ke Direktur (Kondisi 3) |
| rejected_reason | varchar, nullable | Alasan penolakan (Kondisi 4/5/7) |
| paid_at | timestamp, nullable | Waktu pembayaran berhasil diproses |
| timestamps | | |

### `approvals`
Log/jejak setiap keputusan approval (approve/reject) di sepanjang alur.
Satu baris = satu keputusan oleh satu approver pada satu tahap.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| submission_id | bigint, FK → `submissions.id` | Pengajuan terkait |
| user_id | bigint, FK → `users.id` | Approver yang mengambil keputusan |
| role_id | bigint, FK → `roles.id` | Role approver saat keputusan diambil |
| action | varchar | "Approve", "Reject", "Proses Pembayaran", dst |
| catatan | text, nullable | Catatan approval |
| timestamps | | |

### `payments`
Catatan transaksi pembayaran yang diproses Finance (Kondisi 7).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| submission_id | bigint, FK → `submissions.id` | Pengajuan yang dibayar |
| processed_by | bigint, FK → `users.id` | User Finance yang memproses |
| amount | decimal(18,2) | Nilai yang dibayarkan |
| saldo_before | decimal(18,2) | Saldo perusahaan sebelum transaksi |
| saldo_after | decimal(18,2) | Saldo perusahaan setelah transaksi |
| status | enum | `Success` atau `Failed` |
| catatan | text, nullable | |
| paid_at | timestamp, nullable | Waktu pembayaran sukses (null jika Failed) |
| timestamps | | |

### `company_balances` (pendukung)
Menyimpan saldo kas perusahaan yang dipakai Finance untuk pengecekan saldo.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| saldo | decimal(18,2) | Saldo kas perusahaan saat ini |
| timestamps | | |

## 2. Relasi Antar Tabel (ERD Naratif)

```
roles (1) ─────< (N) users
roles (1) ─────< (N) approvals

categories (1) ──< (1) budgets            [1 kategori punya 1 baris budget]
categories (1) ──< (N) submissions

users (1) ──< (N) submissions             [user_id, sebagai pengaju/Staff]
users (1) ──< (N) approvals               [user_id, sebagai approver]
users (1) ──< (N) payments                [processed_by, sebagai petugas Finance]

submissions (1) ──< (N) approvals         [submission_id]
submissions (1) ──< (N) payments          [submission_id, biasanya 1 baris]
```

Penjelasan masing-masing relasi:

1. **`roles` → `users`** (One-to-Many): satu role dapat dimiliki banyak user,
   tapi satu user hanya punya satu role (`users.role_id`). Ini menggantikan
   pendekatan `enum` agar role bisa dikelola sebagai data (bisa tambah role
   baru tanpa migrasi ulang kolom).

2. **`roles` → `approvals`** (One-to-Many): tiap baris approval mencatat role
   apa yang mengambil keputusan tersebut (`approvals.role_id`), terpisah dari
   role user saat ini — berguna sebagai jejak historis meski role user
   berubah di kemudian hari.

3. **`categories` → `budgets`** (One-to-One): satu kategori transaksi punya
   tepat satu baris alokasi anggaran (`budgets.category_id` bersifat
   `unique`). Dipakai untuk Kondisi 4 (cek budget kategori mencukupi).

4. **`categories` → `submissions`** (One-to-Many): satu kategori dapat
   dipakai oleh banyak pengajuan (`submissions.category_id`). Kategori juga
   dipakai untuk menentukan Kondisi 1 (apakah kategori = "PO Produk").

5. **`users` → `submissions`** (One-to-Many): satu Staff dapat membuat
   banyak pengajuan (`submissions.user_id`).

6. **`users` → `approvals`** (One-to-Many): satu user (approver) dapat
   membuat banyak keputusan approval terhadap berbagai pengajuan
   (`approvals.user_id`).

7. **`users` → `payments`** (One-to-Many): satu user Finance dapat memproses
   banyak pembayaran (`payments.processed_by`).

8. **`submissions` → `approvals`** (One-to-Many): satu pengajuan memiliki
   banyak baris approval (satu baris per tahap: SPV/Manager/Direktur/
   Finance), yang bersama-sama membentuk riwayat/jejak audit alur approval
   pengajuan tersebut (`approvals.submission_id`).

9. **`submissions` → `payments`** (One-to-Many, secara bisnis biasanya 1):
   satu pengajuan yang lolos seluruh approval akan diproses Finance dan
   dicatat sebagai satu baris `payments` (`payments.submission_id`). Dibuat
   One-to-Many agar tetap fleksibel bila suatu saat percobaan
   pembayaran perlu dicatat lebih dari sekali (mis. retry).

## 3. Integritas Referensial

- `users.role_id` → `restrictOnDelete()`: role tidak bisa dihapus jika masih
  dipakai oleh user manapun.
- `budgets.category_id` → `cascadeOnDelete()`: budget ikut terhapus jika
  kategori dihapus.
- `submissions.category_id` → `restrictOnDelete()`: kategori tidak bisa
  dihapus jika masih ada pengajuan yang memakainya.
- `approvals.submission_id` → `cascadeOnDelete()`: riwayat approval ikut
  terhapus jika pengajuan induknya dihapus.
- `payments.submission_id` → `cascadeOnDelete()`: riwayat pembayaran ikut
  terhapus jika pengajuan induknya dihapus.

## 4. Catatan Implementasi (Laravel)

Untuk menjaga kompatibilitas dengan controller/view yang sudah ada tanpa
menambah kompleksitas berlebihan, beberapa model Eloquent menyediakan
**accessor & mutator** sebagai jembatan antara nama kolom fisik di database
dengan nama atribut yang lebih ramah dipakai di kode:

- `App\Models\User`: atribut `role` (string, mis. `"staff"`) adalah
  accessor yang mengambil `roleRef->name` dari relasi ke tabel `roles`.
- `App\Models\Pengajuan` (mewakili tabel `submissions`): atribut `kategori`
  (string) adalah accessor/mutator yang membaca/menulis relasi `category`
  ke tabel `categories`.
- `App\Models\Approval` (mewakili tabel `approvals`): atribut `role`
  (string) adalah accessor yang mengambil `roleRef->name`.

Migration dijalankan dengan urutan: `roles` → `categories` → `users` →
`budgets` (+ `company_balances`) → `submissions` → `approvals` → `payments`,
supaya foreign key ke tabel master (`roles`, `categories`) sudah tersedia
lebih dulu.
