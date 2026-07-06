# BukuBerbagi - Sistem Pendonasian Buku Fisik

BukuBerbagi adalah platform berbasis web untuk memfasilitasi pendonasian buku fisik secara online oleh masyarakat umum (Pendonasi), yang selanjutnya diverifikasi dan disalurkan secara offline ke sekolah-sekolah, panti asuhan, atau perpustakaan jalanan oleh pengelola (Admin).

---

## 🎨 UI/UX Theme: Luxury Minimalist / Nordic Gallery
Proyek ini mengadopsi estetika **Luxury Minimalist** yang bersih, terstruktur, dan modern:
* **Tipografi Premium**: Pairing font modern `'Outfit'` (Heading) dan `'Plus Jakarta Sans'` (Body) dari Google Fonts.
* **Palet Warna Organik**: Dominasi warna Linen/Putih Bersih (`#f9fafb` / `#ffffff`) dengan aksen utama **Forest Sage Green** (`#2d5a47`) dan bayangan difusi lembut (*diffusion shadows*).
* **Responsive Layout**: Sepenuhnya responsif menggunakan Bootstrap 5 dengan desain kolom asimetris dan *split-screen* pada halaman autentikasi.

---

## 🛠️ Tech Stack & Prasyarat
* **Bahasa & Logika**: PHP Native (v8.x didukung)
* **CSS & Layout**: Bootstrap 5 (via CDN) & Custom CSS murni
* **JavaScript**: Vanilla JS murni (tanpa jQuery / library eksternal)
* **Database**: MySQL (dijalankan di Laragon / XAMPP)

---

## 📂 Struktur Direktori Proyek
```text
tubes/
├── assets/                 # CSS murni kustom, JS, dan gambar
│   ├── css/
│   │   └── style.css       # Desain global kustom
│   ├── js/
│   │   └── main.js         # Interaksi JS murni
│   └── uploads/            # Folder upload foto buku dari pendonasi
├── config/                 # Pengaturan sistem & database
│   └── database.php        # File koneksi PDO MySQL
├── includes/               # Komponen template berulang
│   ├── header.php          # Navbar & load Google Fonts & CSS
│   └── footer.php          # Footer & load JS
├── views/                  # Halaman spesifik role
│   ├── admin/              # Panel dashboard kurasi & distribusi admin
│   └── pendonasi/          # Panel dashboard donasi & resi pendonasi
├── index.php               # Halaman Beranda Utama & Katalog Publik
├── login.php               # Halaman Masuk (Split-screen)
├── register.php            # Halaman Daftar Pendonasi (Split-screen)
├── logout.php              # Script hapus session
├── setup_db.php            # Script instalasi & seed database otomatis
└── database.sql            # Skema DDL awal database
```

---

## 🚀 Cara Setup Database & Seeder Otomatis

1. Aktifkan server **Apache & MySQL** di Laragon/XAMPP Anda.
2. Pastikan folder proyek diletakkan di dalam folder `www/` (Laragon) atau `htdocs/` (XAMPP).
3. Buka browser Anda dan akses tautan berikut:
   ```text
   http://localhost/tubes/setup_db.php
   ```
4. Script akan otomatis:
   * Membuat database `db_donasi_buku`.
   * Membuat seluruh struktur tabel relasional.
   * Menyuntikkan master kategori buku.
   * Membuat **akun Admin bawaan** dan **3 akun Pendonasi dummy**.
   * Menambahkan **5 sampel donasi** dengan berbagai status (`pending`, `disetujui`, `dikirim`, `diterima`) serta **2 log distribusi** untuk keperluan pengujian.

---

## 🔑 Kredensial Akun Pengujian (Testing Accounts)

### 1. Akun Admin
* **Email**: `admin@donasibuku.com`
* **Kata Sandi**: `admin123`

### 2. Akun Pendonasi Dummy (Dibuat otomatis oleh seeder)
* **Email**: `budi@gmail.com`
* **Kata Sandi**: `password123`
* **Email**: `siti@gmail.com`
* **Kata Sandi**: `password123`

---

## 📋 Alur Bisnis Pengujian Sistem
1. **Daftar/Masuk**: Masuk menggunakan akun Pendonasi `budi@gmail.com`.
2. **Ajukan Donasi**: Klik **Donasikan Buku Baru** -> isi data, unggah foto -> Kirim. (Status donasi awal adalah `Pending`).
3. **Persetujuan Admin**: Logout, lalu masuk sebagai Admin (`admin@donasibuku.com`). Pada dashboard admin, pilih detail donasi budi, klik **Setujui Pengajuan**.
4. **Kirim Buku**: Logout dan masuk kembali sebagai budi. Status donasi budi kini `Disetujui`. Klik **Kirim Buku**, pilih metode kirim (misal: Kurir), isi ekspedisi dan nomor resi, klik Konfirmasi. (Status berubah menjadi `Sedang Dikirim`).
5. **Konfirmasi Fisik**: Masuk kembali sebagai Admin, klik detail donasi budi, klik **Konfirmasi Terima Buku Fisik**. (Status berubah menjadi `Diterima`).
6. **Katalog & Penyaluran**:
   * Buku budi kini otomatis muncul di **Katalog Buku Publik** di halaman depan website ([index.php](index.php)).
   * Di dashboard admin, buka menu **Stok & Inventaris** -> Klik **Salurkan Buku** untuk mencatat pendistribusian buku tersebut secara offline ke penerima target.
