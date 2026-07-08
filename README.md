# Sistem Pengajuan Transaksi Pengeluaran

Aplikasi web untuk mengurus pengajuan transaksi pengeluaran kantor — dari
Staff mengajukan, di-approve berjenjang (SPV/Manager/Direktur sesuai
nilainya), sampai akhirnya dibayar oleh Finance.

**Dipakai:** Laravel 11, MySQL, Bootstrap 5.

---

## Daftar Isi

1. [Yang Perlu Disiapkan Dulu](#1-yang-perlu-disiapkan-dulu)
2. [Cara Install (Sekali di Awal)](#2-cara-install-sekali-di-awal)
3. [Cara Menjalankan Aplikasinya](#3-cara-menjalankan-aplikasinya)
4. [Akun buat Login/Testing](#4-akun-buat-logintesting)
5. [Isi Database-nya Apa Aja](#5-isi-database-nya-apa-aja)
6. [Gimana Cara Kerja Alur Approval-nya](#6-gimana-cara-kerja-alur-approval-nya)
7. [Kalau Ada Error](#7-kalau-ada-error)

---

## 1. Yang Perlu Disiapkan Dulu

Cek dulu 3 hal ini sudah ada di komputer kamu apa belum:

| Yang dicek              | Caranya                                  | Kalau belum ada                                                                                                         |
| ----------------------- | ---------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| PHP (versi 8.2 ke atas) | ketik `php -v` di terminal               | Install [Laragon](https://laragon.org/) (Windows) atau [Herd](https://herd.laravel.com/) (Mac) — sudah sepaket sama PHP |
| Composer                | ketik `composer -v` di terminal          | Laragon/Herd biasanya sudah sepaket juga                                                                                |
| MySQL                   | biasanya nyala otomatis di XAMPP/Laragon | Tinggal nyalain lewat Control Panel-nya                                                                                 |

> Kalau pakai XAMPP: XAMPP di sini cuma dipakai buat **nyalain MySQL-nya
> saja**. Aplikasinya sendiri nanti dijalankan pakai perintah `php artisan
serve`, bukan lewat Apache XAMPP.

---

## 2. Cara Install (Sekali di Awal)

Buka terminal, lalu ketik satu-satu:

```bash
# 1. Ambil source code-nya
git clone <url-repo-github-ini>
cd pengajuan-transaksi-pengeluaran

# 2. Download semua library Laravel yang dibutuhkan
composer install

# 3. Bikin file pengaturan (.env) dari contoh yang sudah disediakan
cp .env.example .env

# 4. Bikin "kunci keamanan" aplikasi
php artisan key:generate
```

Lalu buka file `.env` pakai text editor, cari bagian ini dan sesuaikan
dengan MySQL kamu (kalau pakai XAMPP/Laragon, biasanya default `root` tanpa
password):

```
DB_DATABASE=pengajuan_transaksi
DB_USERNAME=root
DB_PASSWORD=
```

Terakhir, buka phpMyAdmin (`http://localhost/phpmyadmin`), **bikin database
baru** dengan nama **`pengajuan_transaksi`** (harus sama persis dengan yang
di `.env` tadi).

---

## 3. Cara Menjalankan Aplikasinya

```bash
# 1. Bikin semua tabel + isi data awal (akun demo, kategori, dll)
php artisan migrate --seed

# 2. Biar file yang di-upload nanti bisa dibuka lewat browser
php artisan storage:link

# 3. Nyalain servernya
php artisan serve
```

Setelah itu buka browser ke **http://127.0.0.1:8000** — nanti muncul
halaman login. Login pakai salah satu akun di bagian bawah.

> **Mau mulai dari data yang bersih lagi (reset)?** Tinggal jalankan
> `php artisan migrate:fresh --seed` — ini bakal hapus semua data terus
> isi ulang dari awal.

---

## 4. Akun buat Login/Testing

Password semua akun ini sama: **`password`**

| Login sebagai | Email             |
| ------------- | ----------------- |
| Staff         | staff@test.com    |
| SPV           | spv@test.com      |
| Manager       | manager@test.com  |
| Direktur      | direktur@test.com |
| Finance       | finance@test.com  |

---

## 5. Isi Database-nya Apa Aja

Ada 8 tabel:

| Nama Tabel         | Isinya Apa                                              |
| ------------------ | ------------------------------------------------------- |
| `roles`            | Daftar jabatan (Staff, SPV, Manager, Direktur, Finance) |
| `users`            | Daftar akun/orang, masing-masing punya 1 jabatan        |
| `categories`       | Daftar kategori pengeluaran (PO Produk, Marketing, dll) |
| `budgets`          | Jatah anggaran untuk tiap kategori                      |
| `submissions`      | Data pengajuan yang dibuat Staff                        |
| `approvals`        | Catatan tiap kali ada yang approve/reject               |
| `payments`         | Catatan tiap kali Finance membayar                      |
| `company_balances` | Saldo kas perusahaan saat ini                           |

### Gimana tabel-tabel ini saling nyambung

Cara mikirnya gampang: tiap tabel itu kayak **buku catatan**. Kalau satu
buku perlu tahu sesuatu dari buku lain, dia cukup nyimpen **nomornya** aja
(bukan nyalin datanya). Contoh: 1 orang (di tabel `users`) cuma nyimpen
nomor jabatannya, bukan nulis ulang nama jabatannya.

- **Jabatan → Orang**: 1 jabatan bisa dipunya banyak orang, tapi 1 orang cuma punya 1 jabatan.
- **Kategori → Jatah Anggaran**: 1 kategori punya 1 jatah anggaran.
- **Kategori → Pengajuan**: 1 kategori bisa dipakai berkali-kali oleh banyak pengajuan berbeda.
- **Staff → Pengajuan**: 1 Staff bisa bikin banyak pengajuan (jadi riwayatnya).
- **Pengajuan → Catatan Approve/Reject**: setiap pengajuan yang harus lewat beberapa approval (misal SPV lalu Manager) akan punya beberapa baris catatan — satu baris per keputusan.
- **Pengajuan → Catatan Pembayaran**: begitu Finance selesai proses bayar, dicatat 1 baris berisi jumlah yang dibayar dan sisa saldo.

> Analoginya kayak kontak di HP: satu orang bisa disimpan di banyak kontak
> HP orang lain, tapi satu kontak cuma nyimpen satu nomor orang itu.

---

## 6. Gimana Cara Kerja Alur Approval-nya

Tergantung kategori dan nilai pengajuannya:

| Kalau pengajuannya...           | Alurnya lewat siapa aja                           |
| ------------------------------- | ------------------------------------------------- |
| Kategori "PO Produk"            | Langsung ke **Direktur** aja                      |
| Nilainya ≤ Rp 5 juta            | Cukup **SPV** aja                                 |
| Nilainya Rp 5 juta – Rp 10 juta | **SPV** dulu, baru **Manager**                    |
| Nilainya di atas Rp 10 juta     | **Manager** dulu, baru **Direktur** (SPV di-skip) |

Aturan tambahan:

- Kalau jatah anggaran kategorinya sudah habis → pengajuan otomatis **Ditolak**
- Kalau salah satu approver klik **Reject** → langsung **Ditolak**
- Kalau semua approval sudah oke → statusnya jadi **Menunggu Finance**
- Finance akan cek saldo kas perusahaan: cukup → **dibayar (Paid)**, tidak cukup → **Ditolak**

Contoh biar kebayang:

1. Ajukan **Rp 1 juta** → muncul di antrian SPV → approve → langsung ke Finance
2. Ajukan **Rp 6 juta** → muncul di SPV dulu → approve → pindah ke Manager → approve → ke Finance
3. Ajukan **Rp 15 juta** → langsung muncul di Manager (SPV di-skip) → approve → pindah ke Direktur → approve → ke Finance
4. Ajukan kategori **"PO Produk"** → langsung ke Direktur, berapapun nilainya

---

## 7. Kalau Ada Error

| Errornya kira-kira begini           | Cara benerinnya                                                                                          |
| ----------------------------------- | -------------------------------------------------------------------------------------------------------- |
| "No application encryption key"     | Jalankan `php artisan key:generate`                                                                      |
| "Base table or view already exists" | Jalankan `php artisan migrate:fresh --seed`                                                              |
| "This site can't be reached"        | Server belum nyala / terminalnya ke-close. Jalankan lagi `php artisan serve`, jangan ditutup terminalnya |
| File lampiran gak bisa dibuka (404) | Lupa jalankan `php artisan storage:link`                                                                 |
| Mau mulai dari nol lagi             | `php artisan migrate:fresh --seed`                                                                       |
