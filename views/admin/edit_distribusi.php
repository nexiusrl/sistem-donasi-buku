<?php
// views/admin/edit_distribusi.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$distribusi = null;
$donasi_id = 0;
$buku = null;
$stok_maks = 0;

// Ambil info distribusi
if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM distribusi WHERE id = ?");
        $stmt->execute([$id]);
        $distribusi = $stmt->fetch();
        
        if ($distribusi) {
            $donasi_id = $distribusi['donasi_id'];
            
            // Ambil info buku dan sisa stok tanpa menghitung distribusi ini
            $stmt = $pdo->prepare("
                SELECT d.id, d.judul_buku, d.jumlah as total_diterima,
                       COALESCE((SELECT SUM(jumlah_disalurkan) FROM distribusi WHERE donasi_id = d.id AND id != ?), 0) as total_lain_disalurkan
                FROM donasi d
                WHERE d.id = ?
            ");
            $stmt->execute([$id, $donasi_id]);
            $buku = $stmt->fetch();
            
            if ($buku) {
                $stok_maks = $buku['total_diterima'] - $buku['total_lain_disalurkan'];
            }
        }
    } catch (PDOException $e) {
        $distribusi = null;
    }
}

if (!$distribusi || !$buku) {
    header("Location: distribusi.php");
    exit();
}

$error = '';
$success = '';

// Proses edit log distribusi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_edit_distribusi'])) {
    $nama_penerima = trim($_POST['nama_penerima']);
    $tanggal_distribusi = $_POST['tanggal_distribusi'];
    $jumlah_disalurkan = intval($_POST['jumlah_disalurkan']);
    $keterangan = trim($_POST['keterangan']);

    if (empty($nama_penerima) || empty($tanggal_distribusi) || $jumlah_disalurkan <= 0) {
        $error = 'Harap isi semua field formulir dengan benar!';
    } elseif ($jumlah_disalurkan > $stok_maks) {
        $error = 'Jumlah penyaluran melebihi stok buku yang tersedia! Maksimal stok adalah ' . $stok_maks . ' Eks.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE distribusi 
                SET nama_penerima = ?, tanggal_distribusi = ?, jumlah_disalurkan = ?, keterangan = ? 
                WHERE id = ?
            ");
            $stmt->execute([$nama_penerima, $tanggal_distribusi, $jumlah_disalurkan, $keterangan, $id]);
            $_SESSION['success_message'] = 'Log penyaluran buku berhasil diperbarui!';
            header("Location: distribusi.php");
            exit();
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan perubahan log penyaluran: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card card-premium p-4 border-0">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="distribusi.php" class="btn btn-outline-primary btn-sm px-3">
                        <i class="bi bi-arrow-left"></i> Batal
                    </a>
                    <h4 class="fw-bold text-dark mb-0">Ubah Log Penyaluran</h4>
                </div>

                <div class="alert alert-info py-2 small" role="alert">
                    Buku: <strong><?= htmlspecialchars($buku['judul_buku']) ?></strong><br>
                    Total Buku Diterima: <strong><?= htmlspecialchars($buku['total_diterima']) ?> Eks</strong><br>
                    Batas Maksimal Penyaluran Saat Ini: <strong><?= htmlspecialchars($stok_maks) ?> Eks</strong>
                </div>

                <?php if ($error): ?>
                    <script>alert("<?= addslashes($error) ?>");</script>
                <?php endif; ?>

                <form action="edit_distribusi.php?id=<?= $id ?>" method="POST">
                    <div class="mb-3">
                        <label for="nama_penerima" class="form-label">Nama Penerima / Lokasi</label>
                        <input type="text" class="form-control" id="nama_penerima" name="nama_penerima" value="<?= htmlspecialchars($distribusi['nama_penerima']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_disalurkan" class="form-label">Jumlah Disalurkan (Eks)</label>
                        <input type="number" class="form-control" id="jumlah_disalurkan" name="jumlah_disalurkan" min="1" max="<?= $stok_maks ?>" value="<?= htmlspecialchars($distribusi['jumlah_disalurkan']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_distribusi" class="form-label">Tanggal Penyaluran</label>
                        <input type="date" class="form-control" id="tanggal_distribusi" name="tanggal_distribusi" value="<?= htmlspecialchars($distribusi['tanggal_distribusi']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan / Deskripsi</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= htmlspecialchars($distribusi['keterangan']) ?></textarea>
                    </div>

                    <button type="submit" name="submit_edit_distribusi" class="btn btn-primary w-100 py-2 mt-3">
                        <i class="bi bi-save-fill me-1"></i> Simpan Log Penyaluran
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
