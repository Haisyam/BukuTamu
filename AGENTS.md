# AGENTS.md - Panduan Pengembangan Sistem Buku Tamu Digital (Laravel 12)

Dokumen ini berisi konteks arsitektur, aturan penulisan kode, dan referensi desain yang **WAJIB** dipatuhi oleh AI Coding Agent saat membantu mengembangkan proyek ini.

## 1. Konteks Proyek & Tech Stack
- **Nama Proyek:** Sistem Buku Tamu Digital (Model Pencatatan / Guest Log)
- **Framework Utama:** Laravel 12 (PHP 8.2+)
- **Styling & UI:** Tailwind CSS + Blade / Starter Kit Laravel 12
- **Interaktivitas:** Vanilla JS / Alpine.js (khususnya untuk modul Webcam HTML5 `navigator.mediaDevices.getUserMedia`)
- **Konsep Utama:** Pencatatan satu arah (*One-way Guest Logging*). **TIDAK ADA** fitur Check-In / Check-Out maupun perhitungan durasi kunjungan. Tamu mengisi form, mengambil foto webcam, klik simpan, selesai.

## 2. Referensi Desain & Aset Eksternal (PENTING)
Seluruh tampilan antarmuka (UI/UX), palet warna, tipografi, komponen kartu, tombol, dan *layout* harus mengadaptasi gaya visual dari proyek referensi milik developer di path lokal berikut:
- **Path Referensi UI & Assets:** `C:\Users\HaisyamDev\Documents\CODING\Projek Berbayar\AbsensiWangkelang-Astro-Ardi`

### Instruksi Migrasi UI & Assets untuk Agent:
1. **Inspeksi Gaya Visual:** Sebelum membuat halaman baru, periksa struktur konfigurasi Tailwind/CSS, komponen layout, dan palet warna pada folder `src/` di proyek `AbsensiWangkelang-Astro-Ardi`.
2. **Salin Aset Gambar:** Salin aset gambar yang relevan (logo desa/instansi, background, ilustrasi, atau ikon) dari folder `public/` atau `src/assets/` di proyek referensi ke dalam direktori `public/images/` atau `public/assets/` pada proyek Laravel 12 ini.
3. **Konsistensi Komponen:** Konversikan struktur HTML/Tailwind dari komponen Astro (`.astro`) menjadi komponen Laravel Blade (`resources/views/components/`) dengan mempertahankan *class* Tailwind, *border-radius*, *shadow*, dan skema warna yang sama.

## 3. Arsitektur & Konvensi Kode Laravel 12
- **Database & Migration:**
  - Gunakan penamaan tabel bahasa Inggris jamak (`divisions`, `guest_logs`).
  - Hindari kolom yang berhubungan dengan *check-out* atau durasi.
- **Penyimpanan Foto Webcam:**
  - Tangkap gambar dari `<video>` ke `<canvas>`, konversi menjadi format Base64 (`image/jpeg` atau `image/png`).
  - Di sisi *backend* (Controller/Service), dekode string Base64 tersebut dan simpan sebagai file fisik menggunakan `Storage::disk('public')->put('guests/nama_file.jpg', $imageData)`.
  - Simpan *path* relatifnya saja di kolom `photo_path` pada tabel `guest_logs`.
- **Validasi:**
  - Gunakan *Form Request Validation* (contoh: `StoreGuestLogRequest`) untuk memvalidasi input tamu dan memastikan foto webcam telah diambil (atau opsional sesuai pengaturan).
- **Kualitas Kode:**
  - Gunakan *Type Hinting* dan *Return Types* yang ketat pada setiap method di Controller dan Model.
  - Gunakan bahasa Indonesia yang sopan dan jelas untuk semua label UI, pesan *error* validasi, dan *flash message* (notifikasi sukses).

## 4. Batasan (Constraints) - Jangan Lakukan Ini!
- **JANGAN** membuat fitur *Check-Out*, status kunjungan (*waiting/checked-in/checked-out*), atau *QR Code Pass*.
- **JANGAN** menambahkan fitur *Signature Pad* (Tanda Tangan Digital). Bukti fisik hanya menggunakan **Foto Webcam**.
- **JANGAN** mengubah gaya desain secara drastis di luar bahasa desain yang sudah ada pada proyek `AbsensiWangkelang-Astro-Ardi`.