<?php
// views/admin/dashboard.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Ambil statistik admin
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM donasi WHERE status = 'pending'");
    $stats_pending = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM donasi WHERE status = 'dikirim'");
    $stats_dikirim = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT SUM(jumlah) FROM donasi WHERE status = 'diterima'");
    $stats_total_buku = $stmt->fetchColumn() ?: 0;

    // Ambil seluruh daftar donasi untuk ditampilkan
    $stmt = $pdo->query("
        SELECT d.*, k.nama_kategori, u.nama as nama_pendonasi
        FROM donasi d
        JOIN kategori_buku k ON d.kategori_id = k.id
        JOIN users u ON d.user_id = u.id
        ORDER BY d.created_at DESC
    ");
    $daftar_donasi = $stmt->fetchAll();
} catch (PDOException $e) {
    $stats_pending = 0;
    $stats_dikirim = 0;
    $stats_total_buku = 0;
    $daftar_donasi = [];
}
?>

<div class="container py-4">
    <!-- Header Admin -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">Dashboard Admin</h2>
            <p class="text-muted mb-0">Kelola verifikasi donasi dan penyaluran buku fisik.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="stok.php" class="btn btn-outline-primary">
                <i class="bi bi-box-seam me-2"></i> Stok & Inventaris
            </a>
            <a href="distribusi.php" class="btn btn-primary">
                <i class="bi bi-truck me-2"></i> Log Penyaluran/Distribusi
            </a>
        </div>
    </div>

    <!-- Statistik Grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-premium p-3 border-0 bg-white d-flex flex-row align-items-center gap-3">
                <div class="p-3 rounded-circle bg-warning-subtle text-warning">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Butuh Persetujuan</span>
                    <h4 class="fw-bold text-dark mb-0"><?= $stats_pending ?> Pengajuan</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-premium p-3 border-0 bg-white d-flex flex-row align-items-center gap-3">
                <div class="p-3 rounded-circle bg-info-subtle text-info">
                    <i class="bi bi-truck fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Sedang Dikirim</span>
                    <h4 class="fw-bold text-dark mb-0"><?= $stats_dikirim ?> Paket</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-premium p-3 border-0 bg-white d-flex flex-row align-items-center gap-3">
                <div class="p-3 rounded-circle bg-success-subtle text-success">
                    <i class="bi bi-book fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Buku di Gudang</span>
                    <h4 class="fw-bold text-dark mb-0"><?= number_format($stats_total_buku) ?> Eks</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Kelola Donasi -->
    <div class="card card-premium p-4 border-0">
        <h5 class="fw-bold text-dark mb-3">Semua Pengajuan Donasi</h5>
        
        <?php if (empty($daftar_donasi)): ?>
            <div class="text-center py-5 border border-dashed border-dark my-3 bg-light" style="border-width: 2px !important;">
                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block" style="color: var(--color-primary) !important;"></i>
                <h5 class="fw-bold text-dark mb-1">Antrean Bersih</h5>
                <p class="text-muted small mx-auto mb-0" style="max-width: 45ch;">Semua pengajuan donasi dari pendonasi sudah diproses dan diselesaikan.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-premium">
                    <thead>
                        <tr>
                            <th>Pendonasi</th>
                            <th>Buku</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Pengiriman</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftar_donasi as $donasi): ?>
                            <tr>
                                <td>
                                    <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($donasi['nama_pendonasi']) ?></span>
                                    <span class="text-muted small">ID Donatur: <?= $donasi['user_id'] ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($donasi['judul_buku']) ?></span>
                                    <span class="text-muted small">Kondisi: <span class="text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($donasi['kondisi'])) ?></span></span>
                                </td>
                                <td><?= htmlspecialchars($donasi['nama_kategori']) ?></td>
                                <td><?= htmlspecialchars($donasi['jumlah']) ?> Eks</td>
                                <td>
                                    <span class="badge badge-<?= $donasi['status'] ?> text-capitalize px-3 py-2">
                                        <?= htmlspecialchars($donasi['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($donasi['status'] === 'dikirim' || $donasi['status'] === 'diterima'): ?>
                                        <span class="text-capitalize small fw-semibold text-dark"><?= htmlspecialchars($donasi['metode_pengiriman']) ?></span>
                                        <?php if ($donasi['metode_pengiriman'] === 'kurir'): ?>
                                            <span class="d-block text-muted small">Resi: <?= htmlspecialchars($donasi['nomor_resi']) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="detail_donasi.php?id=<?= $donasi['id'] ?>" class="btn btn-outline-primary btn-sm px-3">
                                        <i class="bi bi-eye-fill me-1"></i> Tinjau
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
