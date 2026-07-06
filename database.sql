-- Create Database
CREATE DATABASE IF NOT EXISTS db_donasi_buku;
USE db_donasi_buku;

-- Table users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telp VARCHAR(15) NOT NULL,
    role ENUM('admin', 'pendonasi') NOT NULL DEFAULT 'pendonasi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table kategori_buku
CREATE TABLE IF NOT EXISTS kategori_buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table donasi
CREATE TABLE IF NOT EXISTS donasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul_buku VARCHAR(255) NOT NULL,
    kategori_id INT NOT NULL,
    kondisi ENUM('sangat_baik', 'layak_baca', 'rusak_ringan') NOT NULL,
    jumlah INT NOT NULL CHECK (jumlah > 0),
    foto VARCHAR(255) NOT NULL,
    catatan TEXT NULL,
    status ENUM('pending', 'disetujui', 'ditolak', 'dikirim', 'diterima') NOT NULL DEFAULT 'pending',
    metode_pengiriman ENUM('kurir', 'dropoff', 'cod') NULL,
    ekspedisi VARCHAR(50) NULL,
    nomor_resi VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kategori_id) REFERENCES kategori_buku(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table distribusi
CREATE TABLE IF NOT EXISTS distribusi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donasi_id INT NOT NULL,
    nama_penerima VARCHAR(150) NOT NULL,
    tanggal_distribusi DATE NOT NULL,
    jumlah_disalurkan INT NOT NULL CHECK (jumlah_disalurkan > 0),
    keterangan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donasi_id) REFERENCES donasi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO kategori_buku (nama_kategori) VALUES 
('Fiksi'),
('Non-Fiksi'),
('Pendidikan & Pelajaran'),
('Agama & Filsafat'),
('Anak-Anak'),
('Novel & Sastra'),
('Teknologi & Sains')
ON DUPLICATE KEY UPDATE nama_kategori=VALUES(nama_kategori);

-- Insert default admin user
-- Password is 'admin123'
INSERT INTO users (nama, email, password, no_telp, role) VALUES
('Administrator', 'admin@donasibuku.com', '$2y$10$wN3u5i1ZzUvKkPj.o9/77uZK/FjI9C3C2bF3b82dD9eFhF4E3sK3S', '081234567890', 'admin')
ON DUPLICATE KEY UPDATE email=VALUES(email);
