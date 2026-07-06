<?php
// views/admin/distribusi.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$donasi_id = isset($_GET['donasi_id']) ? intval($_GET['donasi_id']) : 0;
$buku = null;
$stok_tersedia = 0;

// Ambil info buku jika donasi_id disediakan
if ($donasi_id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT d.id, d.judul_buku, d.jumlah as total_diterima,
                   (d.jumlah - COALESCE((SELECT SUM(jumlah_disalurkan) FROM distribusi WHERE donasi_id = d.id), 0)) as stok_tersedia
            FROM donasi d
            WHERE d.id = ? AND d.status = 'diterima'
        ");
        $stmt->execute([$donasi_id]);
        $buku = $stmt->fetch();
        if ($buku) {
            $stok_tersedia = $buku['stok_tersedia'];
        }
    } catch (PDOException $e) {
        $buku = null;
    }
}

$error = '';
$success = '';

// Proses form penyaluran buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_distribusi'])) {
    $donasi_id_form = intval($_POST['donasi_id']);
    $nama_penerima = trim($_POST['nama_penerima']);
    $tanggal_distribusi = $_POST['tanggal_distribusi'];
    $jumlah_disalurkan = intval($_POST['jumlah_disalurkan']);
    $keterangan = trim($_POST['keterangan']);

    // Ambil ulang stok untuk verifikasi server-side
    try {
        $stmt = $pdo->prepare("
            SELECT d.jumlah - COALESCE((SELECT SUM(jumlah_disalurkan) FROM distribusi WHERE donasi_id = d.id), 0)
            FROM donasi d WHERE d.id = ?
        ");
        $stmt->execute([$donasi_id_form]);
        $stok_db = $stmt->fetchColumn();

        if (empty($nama_penerima) || empty($tanggal_distribusi) || $jumlah_disalurkan <= 0) {
            $error = 'Harap isi semua field formulir dengan benar!';
        } elseif ($jumlah_disalurkan > $stok_db) {
            $error = 'Jumlah penyaluran melebihi stok buku yang tersedia!';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO distribusi (donasi_id, nama_penerima, tanggal_distribusi, jumlah_disalurkan, keterangan) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$donasi_id_form, $nama_penerima, $tanggal_distribusi, $jumlah_disalurkan, $keterangan]);
            $success = 'Penyaluran buku fisik berhasil dicatat!';
            
            // Reset state agar form tidak terisi lagi setelah submit berhasil
            $donasi_id = 0;
            $buku = null;
        }
    } catch (PDOException $e) {
        $error = 'Gagal mencatat penyaluran: ' . $e->getMessage();
    }
}

// Ambil seluruh riwayat penyaluran/distribusi
try {
    $stmt = $pdo->query("
        SELECT dist.*, d.judul_buku
        FROM distribusi dist
        JOIN donasi d ON dist.donasi_id = d.id
        ORDER BY dist.created_at DESC
    ");
    $riwayat_distribusi = $stmt->fetchAll();
} catch (PDOException $e) {
    $riwayat_distribusi = [];
}
?>

<div class="container py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="dashboard.php" class="btn btn-outline-primary btn-sm px-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
        <h3 class="fw-bold text-dark mb-0">Manajemen Penyaluran Buku</h3>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Form Penyaluran -->
        <div class="col-lg-5">
            <div class="card card-premium p-4 border-0">
                <h5 class="fw-bold text-dark mb-3">Form Log Penyaluran</h5>
                
                <?php if ($buku): ?>
                    <div class="alert alert-info py-2 small" role="alert">
                        Buku: <strong><?= htmlspecialchars($buku['judul_buku']) ?></strong><br>
                        Stok Tersedia: <strong><?= htmlspecialchars($stok_tersedia) ?> Eks</strong>
                    </div>

                    <form action="distribusi.php" method="POST">
                        <input type="hidden" name="donasi_id" value="<?= $buku['id'] ?>">

                        <div class="mb-3">
                            <label for="nama_penerima" class="form-label">Nama Penerima / Lokasi</label>
                            <input type="text" class="form-control" id="nama_penerima" name="nama_penerima" placeholder="Contoh: SD Negeri 2 Cikutra" required>
                        </div>

                        <div class="mb-3">
                            <label for="jumlah_disalurkan" class="form-label">Jumlah Disalurkan (Eks)</label>
                            <input type="number" class="form-control" id="jumlah_disalurkan" name="jumlah_disalurkan" min="1" max="<?= $stok_tersedia ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_distribusi" class="form-label">Tanggal Penyaluran</label>
                            <input type="date" class="form-control" id="tanggal_distribusi" name="tanggal_distribusi" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan / Deskripsi</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Info tambahan penyerahan buku..."></textarea>
                        </div>

                        <button type="submit" name="submit_distribusi" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-journal-check me-1"></i> Simpan Penyaluran
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-arrow-up-circle fs-2 d-block mb-2"></i>
                        Pilih buku dari menu <a href="stok.php" class="fw-semibold text-primary">Stok & Inventaris</a> terlebih dahulu untuk mencatat log penyaluran baru.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabel Riwayat Penyaluran -->
        <div class="col-lg-7">
            <div class="card card-premium p-4 border-0">
                <h5 class="fw-bold text-dark mb-3">Riwayat Penyaluran Buku</h5>
                
                <?php if (empty($riwayat_distribusi)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                        Belum ada riwayat penyaluran buku fisik yang tercatat.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium">
                            <thead>
                                <tr>
                                    <th>Penerima</th>
                                    <th>Buku</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayat_distribusi as $dist): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($dist['nama_penerima']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($dist['judul_buku']) ?></td>
                                        <td><?= htmlspecialchars($dist['jumlah_disalurkan']) ?> Eks</td>
                                        <td><?= date('d M Y', strtotime($dist['tanggal_distribusi'])) ?></td>
                                        <td class="small text-muted"><?= htmlspecialchars($dist['keterangan'] ?: '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
