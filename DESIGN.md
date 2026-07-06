# Design Specification & Decision Log
## Sistem Pendonasian Buku

### 1. Ringkasan Pemahaman & Asumsi

* **Tujuan**: Memfasilitasi pendaftaran donasi buku fisik oleh Pendonasi secara online dan pendistribusian offline oleh Admin.
* **Peran**:
  - `Admin`: Memvalidasi pengajuan donasi, menerima paket buku, dan mencatat log distribusi offline.
  - `Pendonasi`: Mengisi form donasi, melacak status, dan mengisi data resi/COD pengiriman.
* **Asumsi Skala**: Skala lokal praktikum (< 100 pengguna aktif).
* **Asumsi Keamanan**: Enkripsi password menggunakan `password_hash()` bawaan PHP, otorisasi akses berbasis session PHP.

---

### 2. Log Keputusan (Decision Log)

| Keputusan | Opsi yang Dipertimbangkan | Alasan Memilih Opsi Terpilih |
| :--- | :--- | :--- |
| **Arsitektur Halaman** | PHP Native terstruktur vs. Flat structure | Memilih struktur terpisah (Opsi A) agar kode lebih modular dan mudah dikelola saat jumlah file bertambah. |
| **Penyimpanan Password** | Plaintext vs. `password_hash()` | Memilih `password_hash()` (bcrypt) demi keamanan data dasar pengguna. |
| **Koneksi Database** | MySQLi vs. PDO | Memilih PDO (PHP Data Objects) karena mendukung *Prepared Statements* dengan lebih konsisten untuk mencegah SQL Injection. |

---

### 3. Desain Akhir Struktur Direktori

Struktur folder yang akan diimplementasikan:

```text
tubes/
├── assets/                 # CSS murni kustom, JS, dan gambar
│   ├── css/
│   │   └── style.css       # Styling tambahan di luar Bootstrap
│   ├── js/
│   │   └── main.js         # JavaScript kustom
│   └── uploads/            # Foto buku yang didonasikan
├── config/                 # Pengaturan sistem & database
│   └── database.php        # Koneksi ke MySQL Laragon via PDO
├── includes/               # Komponen template yang digunakan berulang kali
│   ├── header.php          # Navbar & load Bootstrap CSS
│   └── footer.php          # Footer & load Bootstrap JS
├── views/                  # Halaman spesifik berdasarkan role
│   ├── admin/              # Halaman dashboard admin
│   └── pendonasi/          # Halaman dashboard pendonasi
├── index.php               # Halaman publik utama (Landing Page)
├── login.php               # Halaman autentikasi login
├── register.php            # Halaman registrasi akun pendonasi
└── logout.php              # Script destroy session
```

---

### 4. Rencana Manajemen Risiko & Batasan

* **Risiko Duplikasi Pengiriman Resi**: Validasi format resi akan diimplementasikan secara sederhana di sisi klien dan server.
* **Risiko Upload File**: Upload gambar foto buku akan dibatasi hanya pada ekstensi `.jpg`, `.jpeg`, dan `.png` dengan ukuran maksimal 2MB untuk mencegah eksploitasi server.
* **Keamanan Session**: Setiap halaman di bawah `views/admin/` dan `views/pendonasi/` akan memiliki pengecekan `session_start()` di awal baris untuk memastikan pengguna tidak melewati halaman login.
