<?php
// index.php
require_once 'config/database.php';
require_once 'includes/header.php';

// Ambil Statistik
try {
    // Total buku diterima
    $stmt = $pdo->query("SELECT SUM(jumlah) FROM donasi WHERE status = 'diterima'");
    $total_buku = $stmt->fetchColumn() ?: 0;

    // Total pendonasi unik yang donasinya sudah diterima
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM donasi WHERE status = 'diterima'");
    $total_pendonasi = $stmt->fetchColumn() ?: 0;

    // Ambil katalog buku terpopuler/terbaru yang sudah diterima
    $stmt = $pdo->prepare("
        SELECT d.judul_buku, d.jumlah, d.kondisi, d.foto, k.nama_kategori, u.nama as nama_pendonasi
        FROM donasi d
        JOIN kategori_buku k ON d.kategori_id = k.id
        JOIN users u ON d.user_id = u.id
        WHERE d.status = 'diterima'
        ORDER BY d.updated_at DESC
        LIMIT 8
    ");
    $stmt->execute();
    $katalog_buku = $stmt->fetchAll();
} catch (PDOException $e) {
    $total_buku = 0;
    $total_pendonasi = 0;
    $katalog_buku = [];
}
?>

<!-- Hero Section -->
<section class="hero-section text-center text-lg-start">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge bg-teal-light px-3 py-2 fs-6 mb-3" style="color: var(--color-primary) !important;">
                    <i class="bi-heart-fill text-danger me-1"></i> Donasi Buku Fisik
                </span>
                <h1 class="display-4 fw-bold text-dark mb-4">Setiap Buku Memiliki Cerita Baru untuk Dibagikan</h1>
                <p class="lead text-muted mb-4">
                    Kirimkan buku-buku lama Anda yang masih layak baca agar dapat disalurkan secara offline ke sekolah-sekolah, taman bacaan, dan komunitas yang membutuhkan.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="views/pendonasi/dashboard.php" class="btn btn-primary btn-lg">
                            Mulai Donasi Sekarang
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary btn-lg">
                            Daftar Sebagai Pendonasi
                        </a>
                    <?php endif; ?>
                    <a href="#katalog" class="btn btn-outline-primary btn-lg">Lihat Katalog Buku</a>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Editorial Stats Grid -->
                <div class="row g-4">
                    <div class="col-sm-6">
                        <div class="stat-box-editorial text-center text-sm-start">
                            <span class="text-muted small text-uppercase tracking-wider d-block">Buku Terkumpul</span>
                            <h2 class="display-5 fw-bold text-dark mb-0 mt-1"><?= number_format($total_buku) ?>+</h2>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="stat-box-editorial text-center text-sm-start">
                            <span class="text-muted small text-uppercase tracking-wider d-block">Pendonasi Aktif</span>
                            <h2 class="display-5 fw-bold text-dark mb-0 mt-1"><?= number_format($total_pendonasi) ?>+</h2>
                        </div>
                    </div>
                    <div class="col-sm-12 mt-4">
                        <div class="p-4 border-start border-3 bg-white shadow-sm" style="border-color: var(--color-primary) !important; border-radius: 0 var(--border-radius) var(--border-radius) 0;">
                            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-shield-check me-2" style="color: var(--color-primary);"></i>100% Terverifikasi</h5>
                            <p class="text-muted mb-0 small text-start">Setiap buku fisik melewati proses inspeksi kelayakan kuratorial oleh tim admin kami sebelum disalurkan ke perpustakaan tujuan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cara Berdonasi -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark">Alur Pendonasian Buku</h2>
            <p class="text-muted">Prosedur sederhana untuk mengirimkan buku Anda</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4 text-center">
                <div class="p-3">
                    <div class="h1 fw-light mb-3" style="color: var(--color-primary);">01</div>
                    <h5 class="fw-bold text-dark">Daftarkan Donasi</h5>
                    <p class="text-muted small">Buat akun, lalu isi formulir detail buku beserta kondisi fisiknya serta foto pendukung.</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="p-3">
                    <div class="h1 fw-light mb-3" style="color: var(--color-primary);">02</div>
                    <h5 class="fw-bold text-dark">Verifikasi Kelayakan</h5>
                    <p class="text-muted small">Admin akan mengevaluasi pengajuan Anda untuk memverifikasi kelayakan buku.</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="p-3">
                    <div class="h1 fw-light mb-3" style="color: var(--color-primary);">03</div>
                    <h5 class="fw-bold text-dark">Kirim Buku Fisik</h5>
                    <p class="text-muted small">Kirim buku fisik Anda ke alamat kami melalui kurir/COD, dan input nomor resi di sistem.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Katalog Buku Terkumpul -->
<section id="katalog" class="py-5">
    <div class="container py-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-5 pb-3" style="border-bottom: 1px solid var(--border-color);">
            <div>
                <h2 class="fw-bold text-dark mb-1">Katalog Buku yang Terkumpul</h2>
                <p class="text-muted mb-0">Buku-buku fisik yang telah diterima dan siap didistribusikan</p>
            </div>
            <span class="badge bg-teal-light px-3 py-2 mt-3 mt-sm-0" style="color: var(--color-primary) !important;">
                Terbaru
            </span>
        </div>

        <?php if (empty($katalog_buku)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="text-muted mt-3">Belum ada buku terkumpul yang masuk katalog.</p>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php foreach ($katalog_buku as $buku): ?>
                    <div class="col">
                        <div class="card card-premium h-100 overflow-hidden d-flex flex-column justify-content-between">
                            <div>
                                <div style="height: 220px; overflow: hidden; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center; position: relative;">
                                    <?php if ($buku['foto'] && file_exists('assets/uploads/' . $buku['foto'])): ?>
                                        <img src="assets/uploads/<?= htmlspecialchars($buku['foto']) ?>" class="card-img-top w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($buku['judul_buku']) ?>">
                                    <?php else: ?>
                                        <i class="bi bi-book-half text-muted" style="font-size: 4rem; color: #d1d5db !important;"></i>
                                    <?php endif; ?>
                                    <span class="position-absolute top-0 end-0 m-2 badge bg-white text-dark shadow-sm">
                                        <?= htmlspecialchars($buku['jumlah']) ?> Eks
                                    </span>
                                </div>
                                <div class="card-body">
                                    <span class="small fw-bold text-uppercase mb-1 d-block" style="color: var(--color-primary);"><?= htmlspecialchars($buku['nama_kategori']) ?></span>
                                    <h6 class="card-title fw-bold text-dark line-clamp-2 mb-2"><?= htmlspecialchars($buku['judul_buku']) ?></h6>
                                </div>
                            </div>
                            <div class="px-3 pb-3">
                                <div class="pt-2 border-top d-flex justify-content-between align-items-center text-muted small">
                                    <span>Kondisi: <strong class="text-dark text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($buku['kondisi'])) ?></strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
