<?php
// views/admin/stok.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Ambil inventaris stok buku
try {
    $stmt = $pdo->query("
        SELECT d.id, d.judul_buku, d.jumlah as total_diterima, d.kondisi, k.nama_kategori,
               (d.jumlah - COALESCE((SELECT SUM(jumlah_disalurkan) FROM distribusi WHERE donasi_id = d.id), 0)) as stok_tersedia
        FROM donasi d
        JOIN kategori_buku k ON d.kategori_id = k.id
        WHERE d.status = 'diterima'
        ORDER BY d.updated_at DESC
    ");
    $inventaris = $stmt->fetchAll();
} catch (PDOException $e) {
    $inventaris = [];
}
?>

<div class="container py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="dashboard.php" class="btn btn-outline-primary btn-sm px-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
        <h3 class="fw-bold text-dark mb-0">Inventaris Stok Buku</h3>
    </div>

    <!-- Tabel Inventaris -->
    <div class="card card-premium p-4 border-0">
        <h5 class="fw-bold text-dark mb-3">Stok Buku Aktif di Gudang</h5>
        
        <?php if (empty($inventaris)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box2 text-muted fs-1"></i>
                <p class="text-muted mt-3">Belum ada buku berstatus diterima untuk diinventarisasi.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-premium">
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th>Kategori</th>
                            <th>Kondisi</th>
                            <th>Total Donasi</th>
                            <th>Stok Tersedia</th>
                            <th class="text-center">Aksi Penyaluran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventaris as $item): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($item['judul_buku']) ?></td>
                                <td><?= htmlspecialchars($item['nama_kategori']) ?></td>
                                <td class="text-capitalize small"><?= str_replace('_', ' ', htmlspecialchars($item['kondisi'])) ?></td>
                                <td><?= htmlspecialchars($item['total_diterima']) ?> Eks</td>
                                <td>
                                    <?php if ($item['stok_tersedia'] <= 0): ?>
                                        <span class="badge bg-secondary px-3 py-2">Habis Disalurkan</span>
                                    <?php else: ?>
                                        <span class="badge bg-success px-3 py-2"><?= htmlspecialchars($item['stok_tersedia']) ?> Eks</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['stok_tersedia'] > 0): ?>
                                        <a href="distribusi.php?donasi_id=<?= $item['id'] ?>" class="btn btn-primary btn-sm fw-semibold">
                                            <i class="bi bi-send-fill me-1"></i> Salurkan Buku
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Selesai disalurkan</span>
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
