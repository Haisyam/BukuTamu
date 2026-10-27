# 📖 Sistem Informasi Buku Tamu Digital (Guest Log System)

Aplikasi **Buku Tamu Digital** modern berbasis **Laravel 12** yang dirancang untuk menggantikan buku tamu konvensional (kertas) menjadi sistem pencatatan satu arah (*One-Way Guest Logging*) yang cepat, terstruktur, aman, dan terdokumentasi lengkap dengan bukti swafoto (*webcam snapshot*).

Sistem ini sangat cocok diimplementasikan pada meja resepsionis, kantor dinas pemerintahan, kantor desa/kelurahan, instansi swasta, maupun instansi pendidikan.

---

## 🌟 Fitur Utama

### 1. Kiosk & Antarmuka Tamu (Halaman Publik)
- **Live Webcam Snapshot (HTML5 API):** Pengambilan foto bukti kehadiran tamu secara langsung melalui kamera browser tanpa pihak ketiga, langsung dikonversi dan disimpan ke storage server.
- **Auto-Fill Data Tamu Cerdas:** Cukup masukkan nomor WhatsApp / HP, sistem akan otomatis mengenali dan mengisi Nama serta Asal Instansi jika tamu pernah berkunjung sebelumnya.
- **Pilihan Bagian / Divisi Dinamis:** Dropdown daftar tujuan terintegrasi langsung dengan database master bagian.
- **Notifikasi Interaktif:** Menggunakan SweetAlert2 dengan animasi transisi yang modern dan auto-reset form setelah submit berhasil.
- **Keamanan & Rate Limiting:** Dilengkapi proteksi anti-spam *throttle* untuk mencegah pengiriman form beruntun.

### 2. Panel Administrasi (Admin Dashboard)
- **Ringkasan Statistik Real-Time:** 
  - Total tamu hari ini, bulan ini, keseluruhan tamu, dan divisi aktif.
  - Grafik tren kunjungan 7 hari terakhir & proporsi kunjungan per divisi.
- **Manajemen Riwayat Buku Tamu:**
  - Tabel interaktif dengan *thumbnail* foto wajah tamu, tanggal dan jam presisi.
  - **Modal Detail Pop-up:** Tampilan foto beresolusi penuh dan rincian lengkap kunjungan.
  - **Filter Komprehensif:** Filter berdasarkan rentang tanggal (*Date Range*) dan pilihan bidang/divisi.
  - **Pencarian Cepat:** Pencarian berdasarkan nama, kontak, instansi, staf yang dituju, maupun keperluan.
  - **Koreksi & Hapus Data:** Aksi edit data tamu (koreksi typo) serta hapus data yang sekaligus menghapus file foto dari disk penyimpanan.
- **Manajemen Master Divisi / Bidang:**
  - Tambah bagian/bidang baru.
  - Toggle status aktif/non-aktif bagian secara instan.
  - Edit dan hapus data bagian.

### 3. Ekspor & Cetak Laporan Resmi
- **Cetak Laporan PDF (DomPDF):** Format A4 landscape berstandar resmi, lengkap dengan Kop Surat instansi, logo, tabel data kunjungan beserta foto bukti kehadiran tamu, dan kolom tanda tangan pengesahan pejabat.
- **Ekspor Excel / CSV:** Unduhan rekapitulasi lengkap dengan format UTF-8 BOM (anti-karakter rusak saat dibuka di Microsoft Excel) serta proteksi format nomor HP.

---

## 🛠️ Tech Stack & Kebutuhan Sistem

### Teknologi:
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend / Styling:** Tailwind CSS v4, Alpine.js, SweetAlert2
- **PDF Engine:** `barryvdh/laravel-dompdf` (v3.1)
- **Database:** MySQL / MariaDB (atau SQLite untuk kebutuhan pengujian/development)

### Prasyarat Perangkat Lunak:
- **PHP** `>= 8.2` dengan ekstensi aktif:
  - `pdo_mysql` atau `pdo_sqlite`
  - `fileinfo`
  - `gd` (untuk pemrosesan gambar)
  - `mbstring`
  - `openssl`
  - `xml`
- **Composer** `>= 2.x`
- **Node.js** `>= 18.x` dan **NPM**
- **Web Browser** modern dengan izin akses Webcam/Kamera

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

Ikuti langkah-langkah di bawah ini secara berurutan untuk menjalankan proyek dari awal di lingkungan lokal Anda:

### 1. Clone Repositori
Buka terminal / Git Bash, lalu jalankan perintah berikut:
```bash
git clone https://github.com/username/bukutamu.git
```
*(Ganti URL di atas dengan URL repositori Anda jika di-hosting di GitHub/GitLab)*

### 2. Masuk ke Direktori Proyek
```bash
cd bukutamu
```

### 3. Instal Dependensi PHP (Composer)
Unduh seluruh dependensi framework dan paket PHP:
```bash
composer install
```

### 4. Instal Dependensi Frontend (NPM) & Build Aset
Instal pustaka JavaScript/Tailwind dan kompilasi aset frontend:
```bash
npm install
npm run build
```
> **Catatan Pengembangan:** Jika sedang melakukan kustomisasi desain UI, jalankan `npm run dev` pada terminal terpisah untuk fitur live reload/HMR.

### 5. Siapkan Berkas Konfigurasi Lingkungan (`.env`)
Salin berkas `.env.example` menjadi `.env`:
```bash
# Untuk pengguna Windows PowerShell:
copy .env.example .env

# Untuk pengguna Linux / macOS / Git Bash:
cp .env.example .env
```

### 6. Generate Kunci Enkripsi Aplikasi
```bash
php artisan key:generate
```

### 7. Konfigurasi Database
Buka berkas `.env` menggunakan teks editor (misalnya VS Code) dan sesuaikan pengaturan database:

#### Opsi A: Menggunakan MySQL (Disarankan)
Pastikan service MySQL (misal: XAMPP / Laragon) sudah berjalan dan buat database bernama `bukutamu`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bukutamu
DB_USERNAME=root
DB_PASSWORD=
```

#### Opsi B: Menggunakan SQLite
Jika tidak ingin menggunakan MySQL, Anda dapat menggunakan SQLite:
```env
DB_CONNECTION=sqlite
```
*(File database SQLite akan otomatis dibuat di `database/database.sqlite` jika belum ada).*

### 8. Jalankan Migrasi Database & Seeder
Buat seluruh tabel database dan masukkan data awal (Admin, Master Divisi, dan Sampel Tamu):
```bash
php artisan migrate --seed
```
> **Tip:** Jika sewaktu-waktu ingin mereset ulang seluruh database ke kondisi awal bersih:
> ```bash
> php artisan migrate:fresh --seed
> ```

### 9. Buat Tautan Simbolik Penyimpanan (Storage Link)
Langkah ini **sangat penting** agar foto tamu yang diunggah dapat diakses dan ditampilkan di browser:
```bash
php artisan storage:link
```

### 10. Jalankan Server Lokal
Nyalakan server development Laravel:
```bash
php artisan serve
```

Aplikasi sekarang sudah berjalan! Akses melalui browser:
- **Kiosk Form Buku Tamu:** [http://localhost:8000](http://localhost:8000)
- **Halaman Login Admin:** [http://localhost:8000/login](http://localhost:8000/login)

---

## 🔐 Kredensial Login Administrator

Data administrator bawaan yang digenerate oleh `UserSeeder`:

| Parameter | Kredensial |
| :--- | :--- |
| **URL Login** | `http://localhost:8000/login` |
| **Email** | `admin@bukutamu.com` |
| **Password** | `password` |
| **Nama Akun** | Administrator Ekraf |

---

## 🧪 Menjalankan Pengujian Otomatis (Automated Tests)

Proyek ini telah dilengkapi dengan unit & feature tests menyeluruh untuk memastikan seluruh alur (Webcam Base64 upload, auto-fill lookup, CRUD divisi, PDF export, dan CSV export) berfungsi normal:

```bash
php artisan test
```

Semua 10 skenario pengujian dengan 33 asersi akan dijalankan secara otomatis.

---

## 📁 Struktur Direktori Penting

```text
bukutamu/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php        # Autentikasi Admin
│   │   │   ├── DashboardController.php   # Statistik & Chart
│   │   │   ├── DivisionController.php    # CRUD Master Divisi
│   │   │   ├── GuestBookController.php   # Form Kiosk & Upload Base64
│   │   │   └── GuestLogController.php    # Rekap, Filter, PDF & Excel
│   │   └── Middleware/
│   │       └── SecurityHeaders.php       # Proteksi Header Keamanan
│   └── Models/
│       ├── Division.php                  # Model Master Bagian
│       ├── GuestLog.php                  # Model Log Tamu
│       └── User.php                      # Model Admin
├── database/
│   ├── migrations/                       # Skema tabel database
│   └── seeders/                          # Data inisialisasi awal
├── public/
│   ├── images/                           # Logo & aset visual
│   └── storage/                          # Symlink file foto tamu
├── resources/
│   └── views/
│       ├── admin/                        # View Dashboard, Guest Logs, Divisi, PDF
│       ├── auth/                         # View Login
│       ├── guestbook/                    # View Form Kiosk & Webcam
│       └── layouts/                      # Layout Master (Base & Admin)
├── routes/
│   └── web.php                           # Pemetaan URL & Middleware Rate Limit
└── tests/
    └── Feature/                          # Suite Pengujian Otomatis
```

---

## 📄 Lisensi
Proyek ini dikembangkan secara tertutup untuk kebutuhan operasional pencatatan tamu instansi. Hak cipta dilindungi undang-undang.
