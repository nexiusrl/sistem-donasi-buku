<?php
// setup_db.php
// Script otomatis untuk inisialisasi database dan seeding data dummy

$host = "localhost";
$username = "root";
$password = ""; // Default password Laragon kosong
$db_name = "db_donasi_buku";

// Mengatur output agar rapi di browser maupun CLI
$is_cli = php_sapi_name() === "cli";
if (!$is_cli) {
  echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Setup & Seed Database</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            body { background-color: #faf8f5; font-family: system-ui, -apple-system, sans-serif; }
            .setup-card { max-width: 600px; margin: 50px auto; border-radius: 16px; box-shadow: 0 10px 30px rgba(140,115,85,0.05); border: 1px solid #f1ebd9; }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="card setup-card p-4 bg-white border-0">
            <h3 class="fw-bold text-dark mb-3"><i class="bi bi-database-fill-gear text-primary"></i> Setup & Seed Database</h3>
            <div class="border-top pt-3">';
}

function log_message($message, $type = "info")
{
  global $is_cli;
  if ($is_cli) {
    $colors = [
      "success" => "\033[32m[SUKSES]\033[0m",
      "error" => "\033[31m[ERROR]\033[0m",
      "info" => "\033[36m[INFO]\033[0m",
    ];
    echo $colors[$type] . " " . $message . "\n";
  } else {
    $badges = [
      "success" => '<span class="badge bg-success">SUKSES</span>',
      "error" => '<span class="badge bg-danger">ERROR</span>',
      "info" => '<span class="badge bg-info text-dark">INFO</span>',
    ];
    echo '<div class="mb-2">' .
      $badges[$type] .
      " " .
      htmlspecialchars($message) .
      "</div>";
  }
}

try {
  // 1. Koneksi awal ke server MySQL (tanpa memilih database)
  $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
  log_message("Koneksi ke server MySQL berhasil.", "success");

  // 2. Buat database baru jika belum ada
  $pdo->exec(
    "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
  );
  log_message("Database '$db_name' siap digunakan.", "success");

  // 3. Masuk ke database
  $pdo->exec("USE `$db_name`");

  // 4. Buat Tabel users
  $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            no_telp VARCHAR(15) NOT NULL,
            role ENUM('admin', 'pendonasi') NOT NULL DEFAULT 'pendonasi',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
    ");
  log_message("Tabel 'users' berhasil diverifikasi/dibuat.", "success");

  // 5. Buat Tabel kategori_buku
  $pdo->exec("
        CREATE TABLE IF NOT EXISTS kategori_buku (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_kategori VARCHAR(50) NOT NULL UNIQUE
        ) ENGINE=InnoDB;
    ");
  log_message("Tabel 'kategori_buku' berhasil diverifikasi/dibuat.", "success");

  // 6. Buat Tabel donasi
  $pdo->exec("
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
        ) ENGINE=InnoDB;
    ");
  log_message("Tabel 'donasi' berhasil diverifikasi/dibuat.", "success");

  // 7. Buat Tabel distribusi
  $pdo->exec("
        CREATE TABLE IF NOT EXISTS distribusi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            donasi_id INT NOT NULL,
            nama_penerima VARCHAR(150) NOT NULL,
            tanggal_distribusi DATE NOT NULL,
            jumlah_disalurkan INT NOT NULL CHECK (jumlah_disalurkan > 0),
            keterangan TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (donasi_id) REFERENCES donasi(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");
  log_message("Tabel 'distribusi' berhasil diverifikasi/dibuat.", "success");

  // ==========================================
  // SEEDING DATA
  // ==========================================
  log_message("Memulai proses seeding data...", "info");

  // Seed Kategori Buku
  $categories = [
    "Fiksi",
    "Non-Fiksi",
    "Pendidikan & Pelajaran",
    "Agama & Filsafat",
    "Anak-Anak",
    "Novel & Sastra",
    "Teknologi & Sains",
  ];
  $stmt = $pdo->prepare(
    "INSERT INTO kategori_buku (nama_kategori) VALUES (?) ON DUPLICATE KEY UPDATE nama_kategori=VALUES(nama_kategori)",
  );
  foreach ($categories as $cat) {
    $stmt->execute([$cat]);
  }
  log_message("Seeder kategori_buku selesai.", "success");

  // Seed User Admin & Pendonasi Dummy
  // Password semua dummy account adalah 'password123'
  $dummy_password = password_hash("password123", PASSWORD_BCRYPT);

  $users = [
    [
      "Administrator",
      "admin@gmail.com",
      password_hash("admin123", PASSWORD_BCRYPT),
      "081234567890",
      "admin",
    ],
    [
      "Budi Santoso",
      "budi@gmail.com",
      $dummy_password,
      "085711223344",
      "pendonasi",
    ],
    [
      "Siti Aminah",
      "siti@gmail.com",
      $dummy_password,
      "089988776655",
      "pendonasi",
    ],
    [
      "Dewi Lestari",
      "dewi@gmail.com",
      $dummy_password,
      "082133445566",
      "pendonasi",
    ],
  ];

  $stmt = $pdo->prepare(
    "INSERT INTO users (nama, email, password, no_telp, role) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE email=email",
  );
  foreach ($users as $user) {
    $stmt->execute($user);
  }
  log_message("Seeder users selesai (1 Admin, 3 Pendonasi dummy).", "success");

  // Cek ID kategori dan user
  $kategori_ids = $pdo
    ->query("SELECT id FROM kategori_buku")
    ->fetchAll(PDO::FETCH_COLUMN);
  $user_ids = $pdo
    ->query("SELECT id FROM users WHERE role = 'pendonasi'")
    ->fetchAll(PDO::FETCH_COLUMN);

  if (count($user_ids) > 0 && count($kategori_ids) > 0) {
    // Hapus donasi dummy lama agar tidak menumpuk saat rerun seeder
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE donasi;");
    $pdo->exec("TRUNCATE TABLE distribusi;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // Seed data Donasi Dummy
    $donations = [
      [
        $user_ids[0],
        "Kalkulus Edisi 9",
        $kategori_ids[2],
        "sangat_baik",
        3,
        "kalkulus.jpg",
        "Buku kuliah semester 1, halaman masih bersih",
        "diterima",
        "kurir",
        "JNE",
        "REG123456789",
      ],
      [
        $user_ids[1],
        "Laskar Pelangi",
        $kategori_ids[5],
        "layak_baca",
        2,
        "laskar.jpg",
        "Novel legendaris Andrea Hirata",
        "diterima",
        "dropoff",
        null,
        null,
      ],
      [
        $user_ids[2],
        "Kancil yang Cerdik",
        $kategori_ids[4],
        "sangat_baik",
        5,
        "kancil.jpg",
        "Buku dongeng bergambar untuk anak PAUD",
        "pending",
        null,
        null,
        null,
      ],
      [
        $user_ids[0],
        "Fisika Dasar",
        $kategori_ids[2],
        "rusak_ringan",
        1,
        "fisika.jpg",
        "Cover agak lecet tapi isi lengkap",
        "disetujui",
        null,
        null,
        null,
      ],
      [
        $user_ids[1],
        "Belajar PHP Native",
        $kategori_ids[6],
        "sangat_baik",
        2,
        "php.jpg",
        "Sangat cocok untuk pemula",
        "dikirim",
        "kurir",
        "SiCepat",
        "002345678912",
      ],
    ];

    $stmt = $pdo->prepare("
            INSERT INTO donasi (user_id, judul_buku, kategori_id, kondisi, jumlah, foto, catatan, status, metode_pengiriman, ekspedisi, nomor_resi)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
    foreach ($donations as $don) {
      $stmt->execute($don);
    }
    log_message("Seeder donasi selesai (5 Buku donasi dummy).", "success");

    // Seed data Distribusi Dummy (Penyaluran offline)
    $donasi_diterima_ids = $pdo
      ->query("SELECT id FROM donasi WHERE status = 'diterima'")
      ->fetchAll(PDO::FETCH_COLUMN);
    if (count($donasi_diterima_ids) > 0) {
      $distributions = [
        [
          $donasi_diterima_ids[0],
          "Panti Asuhan Kasih Ibu",
          date("Y-m-d"),
          2,
          "Diserahkan langsung ke pengurus panti",
        ],
        [
          $donasi_diterima_ids[1],
          "Perpustakaan Desa Sukamaju",
          date("Y-m-d"),
          1,
          "Diterima oleh kepala desa",
        ],
      ];

      $stmt = $pdo->prepare("
                INSERT INTO distribusi (donasi_id, nama_penerima, tanggal_distribusi, jumlah_disalurkan, keterangan)
                VALUES (?, ?, ?, ?, ?)
            ");
      foreach ($distributions as $dist) {
        $stmt->execute($dist);
      }
      log_message("Seeder log distribusi selesai.", "success");
    }
  }

  log_message(
    "Semua proses setup & seeder database telah berhasil diselesaikan!",
    "success",
  );
} catch (PDOException $e) {
  log_message("Kegagalan proses setup database: " . $e->getMessage(), "error");
}

if (!$is_cli) {
  echo '</div>
            <div class="mt-4 text-center">
                <a href="index.php" class="btn btn-primary"><i class="bi bi-house"></i> Kembali ke Beranda</a>
            </div>
        </div>
    </div>
    </body>
    </html>';
}
?>
