<?php
// views/admin/detail_donasi.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$donation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil Detail Donasi
try {
    $stmt = $pdo->prepare("
        SELECT d.*, k.nama_kategori, u.nama as nama_pendonasi, u.email as email_pendonasi, u.no_telp
        FROM donasi d
        JOIN kategori_buku k ON d.kategori_id = k.id
        JOIN users u ON d.user_id = u.id
        WHERE d.id = ?
    ");
    $stmt->execute([$donation_id]);
    $donasi = $stmt->fetch();
} catch (PDOException $e) {
    $donasi = null;
}

if (!$donasi) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Proses Aksi Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'setujui') {
        $new_status = 'disetujui';
    } elseif ($action === 'tolak') {
        $new_status = 'ditolak';
    } elseif ($action === 'terima') {
        $new_status = 'diterima';
    } else {
        $new_status = '';
    }

    if ($new_status) {
        try {
            $stmt = $pdo->prepare("UPDATE donasi SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $donation_id]);
            header("Location: detail_donasi.php?id=" . $donation_id);
            exit();
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui status donasi: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="dashboard.php" class="btn btn-outline-primary btn-sm px-3">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h3 class="fw-bold text-dark mb-0">Tinjau Detail Pengajuan</h3>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Informasi Donatur & Buku -->
        <div class="col-lg-7">
            <div class="card card-premium p-4 border-0 mb-4">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Informasi Pendonasi</h5>
                <div class="row g-3 small">
                    <div class="col-sm-4 text-muted">Nama Lengkap</div>
                    <div class="col-sm-8 text-dark fw-semibold"><?= htmlspecialchars($donasi['nama_pendonasi']) ?></div>

                    <div class="col-sm-4 text-muted">Alamat Email</div>
                    <div class="col-sm-8 text-dark"><?= htmlspecialchars($donasi['email_pendonasi']) ?></div>

                    <div class="col-sm-4 text-muted">Nomor Telepon/WA</div>
                    <div class="col-sm-8 text-dark"><?= htmlspecialchars($donasi['no_telp']) ?></div>
                </div>
            </div>

            <div class="card card-premium p-4 border-0">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Informasi Buku Donasi</h5>
                <div class="row g-3 small mb-4">
                    <div class="col-sm-4 text-muted">Judul Buku</div>
                    <div class="col-sm-8 text-dark fw-bold"><?= htmlspecialchars($donasi['judul_buku']) ?></div>

                    <div class="col-sm-4 text-muted">Kategori</div>
                    <div class="col-sm-8 text-dark"><?= htmlspecialchars($donasi['nama_kategori']) ?></div>

                    <div class="col-sm-4 text-muted">Jumlah Buku</div>
                    <div class="col-sm-8 text-dark fw-semibold"><?= htmlspecialchars($donasi['jumlah']) ?> Eks</div>

                    <div class="col-sm-4 text-muted">Kondisi Fisik</div>
                    <div class="col-sm-8 text-capitalize text-dark"><?= str_replace('_', ' ', htmlspecialchars($donasi['kondisi'])) ?></div>

                    <div class="col-sm-4 text-muted">Status Sekarang</div>
                    <div class="col-sm-8">
                        <span class="badge badge-<?= $donasi['status'] ?> text-capitalize px-3 py-2">
                            <?= htmlspecialchars($donasi['status']) ?>
                        </span>
                    </div>

                    <div class="col-sm-4 text-muted">Catatan Donatur</div>
                    <div class="col-sm-8 text-dark italic bg-light p-2 rounded">
                        <?= $donasi['catatan'] ? nl2br(htmlspecialchars($donasi['catatan'])) : '<span class="text-muted">Tidak ada catatan</span>' ?>
                    </div>
                </div>

                <!-- Detail Pengiriman jika ada -->
                <?php if ($donasi['status'] === 'dikirim' || $donasi['status'] === 'diterima'): ?>
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Detail Pengiriman Paket</h5>
                    <div class="row g-3 small">
                        <div class="col-sm-4 text-muted">Metode Pengiriman</div>
                        <div class="col-sm-8 text-dark text-capitalize fw-semibold"><?= htmlspecialchars($donasi['metode_pengiriman']) ?></div>

                        <?php if ($donasi['metode_pengiriman'] === 'kurir'): ?>
                            <div class="col-sm-4 text-muted">Ekspedisi/Kurir</div>
                            <div class="col-sm-8 text-dark"><?= htmlspecialchars($donasi['ekspedisi']) ?></div>

                            <div class="col-sm-4 text-muted">Nomor Resi</div>
                            <div class="col-sm-8 text-dark fw-mono"><?= htmlspecialchars($donasi['nomor_resi']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Foto & Panel Aksi -->
        <div class="col-lg-5">
            <div class="card card-premium p-4 border-0 text-center mb-4">
                <h5 class="fw-bold text-dark text-start border-bottom pb-2 mb-3">Foto Kelayakan Buku</h5>
                <div class="bg-light rounded border p-2" style="min-height: 250px; display: flex; align-items: center; justify-content: center;">
                    <?php if ($donasi['foto'] && file_exists('../../assets/uploads/' . $donasi['foto'])): ?>
                        <img src="../../assets/uploads/<?= htmlspecialchars($donasi['foto']) ?>" class="img-fluid rounded shadow-sm" alt="Foto Buku">
                    <?php else: ?>
                        <div class="text-muted">
                            <i class="bi bi-image fs-1 d-block mb-2"></i>
                            Foto tidak tersedia
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Panel Tombol Aksi Admin -->
            <div class="card card-premium p-4 border-0">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Panel Aksi Pengelola</h5>
                
                <?php if ($donasi['status'] === 'pending'): ?>
                    <p class="small text-muted">Tinjau foto kelayakan buku di atas. Tentukan apakah donasi ini disetujui untuk dikirim atau ditolak.</p>
                    <form action="detail_donasi.php?id=<?= $donation_id ?>" method="POST" class="d-flex flex-column gap-2">
                        <button type="submit" name="action" value="setujui" class="btn btn-success w-100 py-2">
                            <i class="bi bi-check-lg me-1"></i> Setujui Pengajuan
                        </button>
                        <button type="submit" name="action" value="tolak" class="btn btn-danger w-100 py-2" onclick="return confirm('Apakah Anda yakin ingin menolak pengajuan ini?')">
                            <i class="bi bi-x-lg me-1"></i> Tolak Pengajuan
                        </button>
                    </form>

                <?php elseif ($donasi['status'] === 'dikirim'): ?>
                    <p class="small text-muted">Pendonasi telah mengirimkan buku fisik. Silakan verifikasi nomor resi/bukti pengiriman terlebih dahulu. Jika fisik buku sudah sampai dan sesuai, konfirmasi penerimaan di bawah ini.</p>
                    <form action="detail_donasi.php?id=<?= $donation_id ?>" method="POST">
                        <button type="submit" name="action" value="terima" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-seam me-1"></i> Konfirmasi Terima Buku Fisik
                        </button>
                    </form>

                <?php elseif ($donasi['status'] === 'diterima'): ?>
                    <div class="alert alert-success mb-0 py-2 text-center" role="alert">
                        <i class="bi bi-check-circle-fill me-1"></i> Buku donasi sudah diterima di gudang dan masuk ke stok aktif.
                    </div>

                <?php elseif ($donasi['status'] === 'disetujui'): ?>
                    <div class="alert alert-warning mb-0 py-2 text-center text-dark" role="alert">
                        <i class="bi bi-hourglass-split me-1"></i> Menunggu pendonasi melakukan pengiriman fisik buku.
                    </div>

                <?php else: ?>
                    <div class="alert alert-danger mb-0 py-2 text-center" role="alert">
                        <i class="bi bi-x-circle-fill me-1"></i> Pengajuan donasi ini ditolak.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
