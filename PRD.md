# Product Requirement Document (PRD)
## Sistem Pendonasian Buku

### 1. Deskripsi Produk & Tujuan Utama
Sistem Pendonasian Buku adalah platform berbasis web yang memfasilitasi pengumpulan buku fisik dari masyarakat umum (Pendonasi). Buku-buku yang terkumpul akan dikelola oleh Admin secara tersentralisasi dan didistribusikan secara offline kepada penerima yang membutuhkan (seperti sekolah, perpustakaan jalanan, atau komunitas baca).

Tujuan utama sistem ini adalah:
- Menyediakan platform transparan dan mudah bagi masyarakat untuk mendonasikan buku yang tidak terpakai.
- Membantu Admin memvalidasi kelayakan buku sebelum dikirimkan.
- Menginventarisasi stok buku yang masuk agar penyaluran offline tepat sasaran.

---

### 2. Pengguna Sistem (User Personas)
Sistem ini memiliki 2 jenis pengguna utama:

#### A. Pendonasi (Donator)
Masyarakat umum yang ingin menyumbangkan buku mereka.
- **Akses**: Registrasi akun mandiri, login/logout, dashboard donasi.
- **Tugas**: Membuat usulan donasi baru, mengisi detail buku, melacak status donasi, mengunggah bukti pengiriman (resi/foto COD).

#### B. Admin
Pengelola internal program pendonasian buku.
- **Akses**: Login via akun yang dibuat langsung di database (atau registrasi khusus admin).
- **Tugas**: Meninjau (menyetujui/menolak) usulan donasi, mengonfirmasi penerimaan fisik buku, mengelola stok inventaris buku, mencatat penyaluran buku offline.

---

### 3. Fitur Utama & Kebutuhan Fungsional

#### 3.1. Landing Page (Halaman Publik)
- Informasi umum tentang program donasi buku.
- Statistik ringkas (Total donasi terkumpul, total pendonasi).
- **Katalog Buku Publik**: Daftar buku yang telah berhasil dikumpulkan (diterima) agar publik tahu buku apa saja yang tersedia untuk disalurkan.

#### 3.2. Autentikasi (Auth)
- Registrasi akun baru untuk Pendonasi.
- Login dan logout untuk Pendonasi dan Admin.
- Enkripsi kata sandi menggunakan `password_hash()` bawaan PHP.

#### 3.3. Modul Pendonasi (Dashboard)
- **Formulir Donasi Baru**:
  - Judul Buku
  - Kategori/Genre (Fiksi, Non-Fiksi, Pendidikan, Anak-anak, dll.)
  - Kondisi Buku (Sangat Baik, Layak Baca, Rusak Ringan)
  - Jumlah Buku (Eksampler)
  - Foto Buku (Upload gambar)
  - Catatan Tambahan (opsional)
- **Status Alur Donasi**:
  1. `Pending` (Menunggu verifikasi admin atas data buku).
  2. `Disetujui` (Siap dikirim oleh pendonasi).
  3. `Ditolak` (Donasi ditolak karena kondisi kurang layak/kategori tidak sesuai).
  4. `Sedang Dikirim` (Pendonasi telah menginput info pengiriman).
  5. `Diterima` (Fisik buku telah sampai dan dikonfirmasi admin).
- **Input Pengiriman**:
  - Pilihan Metode: Kurir (mengisi nama ekspedisi & nomor resi) atau COD / Drop-off langsung ke lokasi.

#### 3.4. Modul Admin (Dashboard)
- **Verifikasi Donasi Masuk**: Admin dapat melihat detail pengajuan donasi dan mengubah status menjadi `Disetujui` atau `Ditolak`.
- **Konfirmasi Penerimaan**: Admin memvalidasi kiriman fisik buku berdasarkan nomor resi/info COD dan mengubah status menjadi `Diterima`.
- **Inventarisasi Stok**: Halaman untuk melihat semua daftar buku yang berstatus `Diterima`.
- **Pencatatan Penyaluran Offline (Log Distribusi)**:
  - Admin dapat mencatat jika buku telah didistribusikan offline (mencatat penerima, tanggal distribusi, dan jumlah buku yang keluar).

---

### 4. Skema Database (MySQL)

Di bawah ini adalah rancangan tabel database untuk diimplementasikan di Laragon:

#### 1. Tabel `users`
Menyimpan data pengguna (Admin & Pendonasi).
- `id` (INT, Primary Key, Auto Increment)
- `nama` (VARCHAR(100))
- `email` (VARCHAR(100), Unique)
- `password` (VARCHAR(255))
- `no_telp` (VARCHAR(15))
- `role` (ENUM('admin', 'pendonasi'), Default: 'pendonasi')
- `created_at` (TIMESTAMP)

#### 2. Tabel `kategori_buku`
Master data kategori buku.
- `id` (INT, Primary Key, Auto Increment)
- `nama_kategori` (VARCHAR(50))

#### 3. Tabel `donasi`
Menyimpan transaksi/usulan donasi.
- `id` (INT, Primary Key, Auto Increment)
- `user_id` (INT, Foreign Key ke `users.id`)
- `judul_buku` (VARCHAR(255))
- `kategori_id` (INT, Foreign Key ke `kategori_buku.id`)
- `kondisi` (ENUM('sangat_baik', 'layak_baca', 'rusak_ringan'))
- `jumlah` (INT)
- `foto` (VARCHAR(255)) -- Menyimpan nama file gambar yang diupload
- `catatan` (TEXT, Nullable)
- `status` (ENUM('pending', 'disetujui', 'ditolak', 'dikirim', 'diterima'), Default: 'pending')
- `metode_pengiriman` (ENUM('kurir', 'dropoff', 'cod'), Nullable)
- `ekspedisi` (VARCHAR(50), Nullable)
- `nomor_resi` (VARCHAR(100), Nullable)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

#### 2. Tabel `distribusi`
Mencatat log penyaluran buku offline.
- `id` (INT, Primary Key, Auto Increment)
- `donasi_id` (INT, Foreign Key ke `donasi.id`) -- Untuk melacak buku mana yang disalurkan
- `nama_penerima` (VARCHAR(150)) -- Nama instansi/komunitas penerima
- `tanggal_distribusi` (DATE)
- `jumlah_disalurkan` (INT)
- `keterangan` (TEXT, Nullable)
- `created_at` (TIMESTAMP)

---

### 5. Kebutuhan Non-Fungsional & Batasan Teknis

- **Bahasa Pemrograman**: PHP Native (versi 8.x didukung oleh Laragon).
- **Framework CSS**: Bootstrap 5 (digunakan melalui CDN atau local assets).
- **Keamanan**:
  - SQL Injection Prevention (menggunakan Prepared Statements / PDO).
  - Cross-Site Scripting (XSS) Prevention (sanitasi output menggunakan `htmlspecialchars()`).
  - Session security (pemeriksaan hak akses login di setiap file PHP).
- **UI/UX**: Desain clean, responsif (mobile-friendly), menggunakan font modern (seperti Inter atau Outfit) dan micro-interactions pada tombol/navigasi.
