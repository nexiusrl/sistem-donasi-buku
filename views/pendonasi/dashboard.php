<?php
// views/pendonasi/dashboard.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pendonasi') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil riwayat donasi pendonasi ini
try {
    $stmt = $pdo->prepare("
        SELECT d.*, k.nama_kategori 
        FROM donasi d
        JOIN kategori_buku k ON d.kategori_id = k.id
        WHERE d.user_id = ?
        ORDER BY d.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $riwayat_donasi = $stmt->fetchAll();
} catch (PDOException $e) {
    $riwayat_donasi = [];
}
?>

<div class="container py-4">
    <!-- Header Dashboard -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">Dashboard Pendonasi</h2>
            <p class="text-muted mb-0">Kelola pengajuan donasi buku Anda di sini.</p>
        </div>
        <a href="tambah_donasi.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i> Donasikan Buku Baru
        </a>
    </div>

    <!-- Statistik Singkat -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-premium p-3 border-0 bg-white text-center">
                <span class="text-muted small">Total Pengajuan</span>
                <h4 class="fw-bold text-dark mt-1 mb-0"><?= count($riwayat_donasi) ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-premium p-3 border-0 bg-white text-center">
                <span class="text-muted small">Diterima</span>
                <h4 class="fw-bold text-success mt-1 mb-0">
                    <?= count(array_filter($riwayat_donasi, fn($d) => $d['status'] === 'diterima')) ?>
                </h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-premium p-3 border-0 bg-white text-center">
                <span class="text-muted small">Menunggu Persetujuan</span>
                <h4 class="fw-bold text-warning mt-1 mb-0">
                    <?= count(array_filter($riwayat_donasi, fn($d) => $d['status'] === 'pending')) ?>
                </h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-premium p-3 border-0 bg-white text-center">
                <span class="text-muted small">Sedang Dikirim</span>
                <h4 class="fw-bold mt-1 mb-0" style="color: var(--primary-color);">
                    <?= count(array_filter($riwayat_donasi, fn($d) => $d['status'] === 'dikirim')) ?>
                </h4>
            </div>
        </div>

    </div>

    <!-- Riwayat Donasi Card -->
    <div class="card card-premium p-4 border-0">
        <h5 class="fw-bold text-dark mb-3">Riwayat Pengajuan Donasi</h5>
        
        <?php if (empty($riwayat_donasi)): ?>
            <div class="text-center py-5 border border-dashed border-dark my-3 bg-light" style="border-width: 2px !important;">
                <i class="bi bi-journal-x fs-1 text-muted mb-3 d-block" style="color: var(--color-gold) !important;"></i>
                <h5 class="fw-bold text-dark mb-1">Belum Ada Pengajuan</h5>
                <p class="text-muted small mx-auto mb-4" style="max-width: 45ch;">Kotak donasi Anda masih kosong. Mulai dengan mendaftarkan buku pertama Anda untuk disalurkan ke perpustakaan jalanan.</p>
                <a href="tambah_donasi.php" class="btn btn-primary btn-sm">Mulai Donasi Pertama</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-premium">
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th>Detail Pengiriman</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat_donasi as $donasi): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($donasi['foto'] && file_exists('../../assets/uploads/' . $donasi['foto'])): ?>
                                            <img src="../../assets/uploads/<?= htmlspecialchars($donasi['foto']) ?>" width="45" height="45" class="object-fit-cover rounded border" alt="">
                                        <?php else: ?>
                                            <div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                <i class="bi bi-book text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($donasi['judul_buku']) ?></span>
                                            <span class="text-muted small">Diajukan: <?= date('d M Y', strtotime($donasi['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($donasi['nama_kategori']) ?></td>
                                <td><?= htmlspecialchars($donasi['jumlah']) ?> Eks</td>
                                <td>
                                    <span class="text-capitalize small"><?= str_replace('_', ' ', htmlspecialchars($donasi['kondisi'])) ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $donasi['status'] ?> text-capitalize px-3 py-2">
                                        <?= htmlspecialchars($donasi['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($donasi['status'] === 'dikirim' || $donasi['status'] === 'diterima'): ?>
                                        <div class="small">
                                            <span class="d-block text-dark fw-semibold text-capitalize">Metode: <?= htmlspecialchars($donasi['metode_pengiriman']) ?></span>
                                            <?php if ($donasi['metode_pengiriman'] === 'kurir'): ?>
                                                <span class="text-muted">Resi: <?= htmlspecialchars($donasi['nomor_resi']) ?> (<?= htmlspecialchars($donasi['ekspedisi']) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($donasi['status'] === 'disetujui'): ?>
                                        <a href="kirim_buku.php?id=<?= $donasi['id'] ?>" class="btn btn-warning btn-sm fw-semibold text-white px-3">
                                            <i class="bi bi-send-fill me-1"></i> Kirim Buku
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Tidak ada aksi</span>
                                    <?php endif; ?>
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
