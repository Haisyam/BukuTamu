# Product Requirements Document (PRD)
**Nama Proyek:** Sistem Informasi Buku Tamu Digital (Guest Log System)  
**Platform:** Web Application (Laravel 12)  
**Referensi Desain:** `AbsensiWangkelang-Astro-Ardi`

---

## 1. Latar Belakang & Tujuan
Menggantikan buku tamu fisik (kertas) menjadi sistem pencatatan digital satu arah yang cepat, rapi, dan terdokumentasi dengan bukti foto wajah melalui kamera (webcam). Sistem ini dirancang agar mudah digunakan oleh tamu di meja pelayanan/resepsionis serta memudahkan admin dalam merekap dan mencetak laporan kunjungan berkala.

## 2. Pengguna Sistem (User Roles)
1. **Tamu (Public / Tanpa Login):** Mengisi form buku tamu dan mengambil swafoto melalui webcam pada perangkat yang disediakan (atau perangkat sendiri).
2. **Administrator / Petugas (Auth Login):** Mengelola data master bagian/tujuan, memantau daftar tamu, memfilter riwayat kunjungan, serta mencetak laporan (PDF/Excel).

---

## 3. Spesifikasi Fitur Utama

### 3.1. Halaman Depan: Form Pencatatan Buku Tamu (Kiosk / Public View)
Halaman utama (`/`) yang langsung menampilkan form pencatatan dengan estetika UI yang selaras dengan proyek *AbsensiWangkelang*.
- **Input Form Pencatatan:**
  - **Nomor HP / WhatsApp:** Dilengkapi fitur *Auto-fill* (jika nomor HP pernah tercatat sebelumnya, Nama dan Asal Instansi otomatis terisi).
  - **Nama Lengkap** *(Wajib)*
  - **Asal Instansi / Alamat / Organisasi** *(Wajib)*
  - **Tujuan Bagian / Bidang** *(Dropdown dari tabel `divisions` - Wajib)*
  - **Bertemu Dengan Siapa** *(Input teks nama pejabat/staf - Opsional/Wajib)*
  - **Keperluan Kunjungan** *(Textarea - Wajib)*
- **Modul Kamera (Webcam Snapshot):**
  - Tampilan *live preview* kamera menggunakan HTML5 MediaDevices API.
  - Tombol **"Ambil Foto"** untuk membekukan gambar dan tombol **"Foto Ulang"** (*Retake*) jika gambar kurang jelas.
  - Hasil tangkapan kamera dikonversi ke Base64 dan dikirim bersama form.
- **Respon Submit:**
  - Setelah klik **"Simpan Buku Tamu"**, sistem menampilkan notifikasi sukses (*SweetAlert / Toast*) selama 3 detik dan me-reset form kembali kosong untuk tamu berikutnya.

### 3.2. Halaman Belakang: Dashboard & Manajemen Admin
Menggunakan proteksi autentikasi bawaan Starter Kit Laravel 12.
- **Ringkasan Statistik (Dashboard):**
  - Total Tamu Hari Ini.
  - Total Tamu Bulan Ini.
  - Total Keseluruhan Tamu.
  - Grafik sederhana kunjungan 7 hari terakhir & proporsi bagian/bidang yang paling sering dikunjungi.
- **Manajemen Data Tamu (`Guest Logs`):**
  - Tabel daftar tamu lengkap dengan *thumbnail* foto webcam, waktu kedatangan (Tanggal & Jam), nama, instansi, tujuan, dan keperluan.
  - Modal *Pop-up* detail untuk melihat foto wajah tamu dalam ukuran penuh.
  - Fitur Filter berdasarkan rentang tanggal (*Start Date* - *End Date*) dan filter berdasarkan Bagian/Divisi.
  - Fitur Pencarian berdasarkan Nama atau Instansi.
  - Aksi Hapus (dan Edit jika ada kesalahan ketik).
- **Manajemen Master Bagian/Bidang (`Divisions`):**
  - CRUD sederhana untuk daftar bagian/bidang/perangkat yang dapat ditemui.
- **Export & Cetak Laporan:**
  - **Export PDF:** Mencetak tabel buku tamu resmi lengkap dengan kop surat/logo (dari aset *AbsensiWangkelang*), foto tamu, dan kolom tanda tangan pimpinan di bagian bawah laporan.
  - **Export Excel:** Mengunduh rekapitulasi data kunjungan dalam format `.xlsx` / `.csv`.

---

## 4. Desain Skema Database

### Tabel 1: `divisions` (Master Tujuan/Bagian)
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `bigint` (PK) | Auto Increment |
| `name` | `varchar(100)` | Nama bagian/jabatan (Contoh: Kepala Desa/Kantor, Sekretariat, Pelayanan) |
| `is_active` | `boolean` | Default `true` |
| `created_at` | `timestamp` | Waktu dibuat |
| `updated_at` | `timestamp` | Waktu diubah |

### Tabel 2: `guest_logs` (Data Pencatatan Tamu)
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `bigint` (PK) | Auto Increment |
| `division_id` | `foreignId` | Relasi ke `divisions.id` (`nullable`, `nullOnDelete`) |
| `name` | `varchar(150)` | Nama lengkap tamu |
| `phone` | `varchar(25)` | Nomor HP/WA (di-index untuk pencarian *auto-fill*) |
| `institution` | `varchar(150)` | Asal instansi / alamat tamu |
| `person_to_meet`| `varchar(150)` | Nama staf/pejabat yang ditemui (`nullable`) |
| `purpose` | `text` | Detail keperluan kunjungan |
| `photo_path` | `varchar(255)` | Path file foto di storage (contoh: `guests/2026-10-21_abc.jpg`) |
| `visit_date` | `date` | Tanggal kunjungan (di-index untuk filter laporan) |
| `visit_time` | `time` | Jam pencatatan kunjungan |
| `created_at` | `timestamp` | Waktu dibuat |
| `updated_at` | `timestamp` | Waktu diubah |

---

## 5. Rencana Endpoint / Routing (`routes/web.php`)

### Public Routes (Tanpa Login)
- `GET /` -> Menampilkan halaman form buku tamu + webcam.
- `POST /guest-book` -> Menyimpan data tamu dan foto Base64.
- `GET /api/guest-lookup?phone={phone}` -> Endpoint JSON untuk fitur *auto-fill* data tamu lama berdasarkan nomor HP.

### Protected Routes (Middleware `auth`)
- `GET /dashboard` -> Statistik & ringkasan kunjungan.
- `GET /guest-logs` -> Daftar riwayat buku tamu + filter tanggal.
- `DELETE /guest-logs/{guestLog}` -> Menghapus data buku tamu.
- `GET /guest-logs/export/pdf` -> Cetak laporan PDF sesuai filter tanggal.
- `GET /guest-logs/export/excel` -> Unduh laporan Excel sesuai filter tanggal.
- `RESOURCE /divisions` -> CRUD master bagian/tujuan.

---

## 6. Tahapan Pengerjaan (Development Milestones)
1. **Tahap 1 (Setup & Aset):** Menyalin aset gambar dan mengadaptasi konfigurasi Tailwind/UI dari proyek `AbsensiWangkelang-Astro-Ardi` ke Laravel 12.
2. **Tahap 2 (Database):** Membuat *Migration*, *Model*, dan *Seeder* untuk tabel `divisions` dan `guest_logs`.
3. **Tahap 3 (Halaman Depan & Webcam):** Membangun UI form buku tamu, integrasi kamera HTML5, penyimpanan gambar Base64 ke `storage/app/public/guests`, dan fitur *auto-fill* nomor HP.
4. **Tahap 4 (Dashboard Admin):** Membuat halaman statistik, tabel log kunjungan dengan filter tanggal, dan CRUD bagian/divisi.
5. **Tahap 5 (Pelaporan):** Menambahkan fitur cetak laporan PDF (lengkap dengan foto tamu) dan ekspor data.