<?php
// views/admin/edit_kategori.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$kategori = null;

// Ambil Kategori Terkait
if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM kategori_buku WHERE id = ?");
        $stmt->execute([$id]);
        $kategori = $stmt->fetch();
    } catch (PDOException $e) {
        $kategori = null;
    }
}

if (!$kategori) {
    header("Location: kategori.php");
    exit();
}

$error = '';
$success = '';

// Proses update kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_kategori'])) {
    $nama_kategori = trim($_POST['nama_kategori']);

    if (empty($nama_kategori)) {
        $error = 'Nama kategori tidak boleh kosong!';
    } elseif (strlen($nama_kategori) > 50) {
        $error = 'Nama kategori maksimal 50 karakter!';
    } else {
        try {
            // Cek duplikasi nama jika namanya diubah
            $stmt = $pdo->prepare("SELECT id FROM kategori_buku WHERE nama_kategori = ? AND id != ?");
            $stmt->execute([$nama_kategori, $id]);
            if ($stmt->fetch()) {
                $error = 'Kategori "' . htmlspecialchars($nama_kategori) . '" sudah digunakan!';
            } else {
                $stmt = $pdo->prepare("UPDATE kategori_buku SET nama_kategori = ? WHERE id = ?");
                $stmt->execute([$nama_kategori, $id]);
                $success = 'Kategori berhasil diubah!';
                // Refresh data kategori
                $kategori['nama_kategori'] = $nama_kategori;
            }
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui kategori: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card card-premium p-4 border-0">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="kategori.php" class="btn btn-outline-primary btn-sm px-3">
                        <i class="bi bi-arrow-left"></i> Batal
                    </a>
                    <h4 class="fw-bold text-dark mb-0">Ubah Kategori</h4>
                </div>

                <?php if ($error): ?>
                    <script>alert("<?= addslashes($error) ?>");</script>
                <?php endif; ?>

                <?php if ($success): ?>
                    <script>alert("<?= addslashes($success) ?>");</script>
                <?php endif; ?>

                <form action="edit_kategori.php?id=<?= $id ?>" method="POST">
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= htmlspecialchars($kategori['nama_kategori']) ?>" required>
                    </div>
                    <button type="submit" name="edit_kategori" class="btn btn-primary w-100 py-2 mt-2">
                        <i class="bi bi-save-fill me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
