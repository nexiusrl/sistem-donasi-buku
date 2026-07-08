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
    <?php
    $css_version = '1.0';
    $css_file = $base_path . 'assets/css/style.css';
    if (file_exists($css_file)) {
        $css_version = filemtime($css_file);
    }
    ?>
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css?v=<?= $css_version ?>">
</head>
<body class="<?= $is_in_subfolder ? 'dashboard-layout-body' : '' ?>">

<?php if (!$is_in_subfolder): ?>
    <!-- Navbar Premium (Halaman Publik) -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= $base_path ?>index.php">
                <i class="bi bi-book-half fs-3"></i>
                <span>BukuBerbagi</span>
            </a>
            <button class="navbar-toggler" type="button" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <span class="me-2 text-dark d-none d-md-inline">Halo, <strong><?= htmlspecialchars($_SESSION["nama"]) ?></strong></span>
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
    <main class="py-4">
<?php else: ?>
    <!-- Mobile Header (Hanya tampil di Mobile untuk Dashboard) -->
    <header class="mobile-dashboard-header d-lg-none">
        <div class="container-fluid d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
            <a class="navbar-brand-mobile" href="<?= $base_path ?>index.php">
                <i class="bi bi-book-half fs-4" style="color: var(--color-primary);"></i>
                <span class="fw-bold">BukuBerbagi</span>
            </a>
            <button class="btn btn-outline-primary btn-sm px-3 sidebar-toggle-btn" type="button">
                <i class="bi bi-list fs-5"></i>
            </button>
        </div>
    </header>

    <!-- Dashboard Panel Layout Wrapper -->
    <div class="dashboard-wrapper">
        <!-- Sidebar Backdrop for Mobile -->
        <div class="sidebar-backdrop"></div>

        <!-- Sidebar Panel Premium -->
        <aside class="sidebar-panel">
            <div class="sidebar-header border-bottom">
                <a href="<?= $base_path ?>index.php" class="sidebar-brand">
                    <i class="bi bi-book-half fs-3" style="color: var(--color-primary);"></i>
                    <span>BukuBerbagi</span>
                </a>
            </div>
            
            <div class="sidebar-menu-wrapper">
                <ul class="sidebar-menu">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="menu-label">Menu Admin</li>
                        <li>
                            <a href="<?= $base_path ?>views/admin/dashboard.php" class="<?= strpos($script_name, 'admin/dashboard.php') !== false ? 'active' : '' ?>">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base_path ?>views/admin/stok.php" class="<?= strpos($script_name, 'admin/stok.php') !== false ? 'active' : '' ?>">
                                <i class="bi bi-box-seam"></i> Stok & Inventaris
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base_path ?>views/admin/distribusi.php" class="<?= strpos($script_name, 'admin/distribusi.php') !== false || strpos($script_name, 'admin/edit_distribusi.php') !== false ? 'active' : '' ?>">
                                <i class="bi bi-truck"></i> Log Distribusi
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base_path ?>views/admin/kategori.php" class="<?= strpos($script_name, 'admin/kategori.php') !== false || strpos($script_name, 'admin/edit_kategori.php') !== false ? 'active' : '' ?>">
                                <i class="bi bi-tags"></i> Kategori Buku
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base_path ?>views/admin/users.php" class="<?= strpos($script_name, 'admin/users.php') !== false ? 'active' : '' ?>">
                                <i class="bi bi-people"></i> Manajemen Pengguna
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="menu-label">Menu Pendonasi</li>
                        <li>
                            <a href="<?= $base_path ?>views/pendonasi/dashboard.php" class="<?= strpos($script_name, 'pendonasi/dashboard.php') !== false || strpos($script_name, 'pendonasi/edit_donasi.php') !== false || strpos($script_name, 'pendonasi/kirim_buku.php') !== false ? 'active' : '' ?>">
                                <i class="bi bi-person-workspace"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base_path ?>views/pendonasi/tambah_donasi.php" class="<?= strpos($script_name, 'pendonasi/tambah_donasi.php') !== false ? 'active' : '' ?>">
                                <i class="bi bi-plus-circle"></i> Donasi Baru
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base_path ?>views/pendonasi/profil.php" class="<?= strpos($script_name, 'pendonasi/profil.php') !== false ? 'active' : '' ?>">
                                <i class="bi bi-person-gear"></i> Profil Saya
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Sidebar User Profile Footer -->
            <div class="sidebar-footer border-top">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="user-info">
                        <span class="user-name d-block text-dark fw-bold"><?= htmlspecialchars($_SESSION['nama']) ?></span>
                        <span class="user-role d-block text-muted text-uppercase tracking-wider"><?= $_SESSION['role'] ?></span>
                    </div>
                    <a href="<?= $base_path ?>logout.php" class="btn btn-danger btn-sm px-2 py-1" title="Log Keluar" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <i class="bi bi-box-arrow-right fs-6"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Right Main Panel Content -->
        <main class="dashboard-content-main">
<?php endif; ?>
