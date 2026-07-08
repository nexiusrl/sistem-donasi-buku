<?php
// config/database.php

// Deteksi lingkungan otomatis (localhost vs hosting)
$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
// Hilangkan port jika ada (misal: localhost:8000 menjadi localhost)
$host_name = explode(':', $http_host)[0];

if (in_array($host_name, ["localhost", "127.0.0.1"]) || php_sapi_name() === 'cli') {
  $host = "localhost";
  $db_name = "db_donasi_buku";
  $username = "root";
  $password = ""; // Default password Laragon kosong
} else {
  // Konfigurasi untuk InfinityFree (Sesuaikan dengan info di Client Area Anda)
  $host = "sql300.infinityfree.com"; // Ganti dengan MySQL Hostname Anda
  $db_name = "if0_42351389_db_donasi_buku"; // Ganti dengan Nama Database Anda
  $username = "if0_42351389"; // Ganti dengan Username Hosting Anda
  $password = "T6qmDpePE1fDKE"; // Ganti dengan Password Akun/Hosting Anda
}

try {
  $pdo = new PDO(
    "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
    $username,
    $password,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ],
  );
} catch (PDOException $e) {
  die("Koneksi database gagal: " . $e->getMessage());
}
?>
