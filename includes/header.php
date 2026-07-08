<?php
// includes/header.php
require_once __DIR__ . '/session.php';

// Menentukan relative path ke root folder secara dinamis
$script_name = $_SERVER["SCRIPT_NAME"];
$is_in_subfolder =
  strpos($script_name, "/views/admin/") !== false ||
  strpos($script_name, "/views/pendonasi/") !== false;
$base_path = $is_in_subfolder ? "../../" : "./";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pendonasian Buku</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
</head>
<body>

    <!-- Navbar Premium -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= $base_path ?>index.php">
                <i class="bi bi-book-half fs-3"></i>
                <span>BukuBerbagi</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_path ?>views/admin/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_path ?>views/admin/stok.php">Stok</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_path ?>views/admin/distribusi.php">Distribusi</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_path ?>views/admin/kategori.php">Kategori</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_path ?>views/admin/users.php">Pengguna</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_path ?>views/pendonasi/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_path ?>views/pendonasi/tambah_donasi.php">Donasi Baru</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_path ?>views/pendonasi/profil.php">Profil Saya</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <span class="me-2 text-dark d-none d-md-inline">Halo, <strong><?= htmlspecialchars(
                          $_SESSION["nama"],
                        ) ?></strong></span>
                        <?php if ($_SESSION["role"] === "admin"): ?>
                            <a href="<?= $base_path ?>views/admin/dashboard.php" class="btn btn-outline-primary">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard Admin
                            </a>
                        <?php else: ?>
                            <a href="<?= $base_path ?>views/pendonasi/dashboard.php" class="btn btn-outline-primary">
                                <i class="bi bi-person-workspace me-1"></i> Dashboard Saya
                            </a>
                        <?php endif; ?>
                        <a href="<?= $base_path ?>logout.php" class="btn btn-danger btn-sm px-3">
                            <i class="bi bi-box-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?= $base_path ?>login.php" class="btn btn-outline-primary">Masuk</a>
                        <a href="<?= $base_path ?>register.php" class="btn btn-primary">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Wrapper untuk isi halaman -->
    <main class="py-4">
