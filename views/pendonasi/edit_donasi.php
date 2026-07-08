<?php
// views/pendonasi/edit_donasi.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pendonasi') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$donation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$donasi = null;

// Ambil data donasi
if ($donation_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM donasi WHERE id = ? AND user_id = ?");
        $stmt->execute([$donation_id, $user_id]);
        $donasi = $stmt->fetch();
    } catch (PDOException $e) {
        $donasi = null;
    }
}

if (!$donasi) {
    header("Location: dashboard.php");
    exit();
}

// Hanya boleh mengedit jika statusnya 'pending'
if ($donasi['status'] !== 'pending') {
    $_SESSION['error_message'] = 'Pengajuan donasi yang sudah diproses tidak dapat diubah!';
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Ambil data kategori untuk select option
try {
    $stmt = $pdo->query("SELECT * FROM kategori_buku ORDER BY nama_kategori ASC");
    $kategori_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $kategori_list = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_buku = trim($_POST['judul_buku']);
    $kategori_id = $_POST['kategori_id'];
    $kondisi = $_POST['kondisi'];
    $jumlah = intval($_POST['jumlah']);
    $catatan = trim($_POST['catatan']);
    
    $foto_name = $donasi['foto']; // Default pakai foto lama
    
    // Check if new file uploaded
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_name = $_FILES['foto']['name'];
        $file_size = $_FILES['foto']['size'];
        
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $error = 'Ekstensi file foto tidak didukung! Gunakan format JPG, JPEG, atau PNG.';
        } elseif ($file_size > 2 * 1024 * 1024) { // 2MB
            $error = 'Ukuran foto maksimal adalah 2MB!';
        } else {
            $upload_dir = '../../assets/uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique name
            $new_foto_name = uniqid('buku_', true) . '.' . $file_ext;
            $upload_path = $upload_dir . $new_foto_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Hapus foto lama jika ada
                if ($donasi['foto'] && file_exists($upload_dir . $donasi['foto'])) {
                    unlink($upload_dir . $donasi['foto']);
                }
                $foto_name = $new_foto_name;
            } else {
                $error = 'Gagal mengunggah foto buku baru!';
            }
        }
    }

    // Masukkan ke database jika tidak ada error
    if (empty($error)) {
        if (empty($judul_buku) || empty($kategori_id) || empty($kondisi) || $jumlah <= 0) {
            $error = 'Harap isi semua data dengan benar!';
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE donasi 
                    SET judul_buku = ?, kategori_id = ?, kondisi = ?, jumlah = ?, foto = ?, catatan = ? 
                    WHERE id = ? AND user_id = ? AND status = 'pending'
                ");
                $stmt->execute([$judul_buku, $kategori_id, $kondisi, $jumlah, $foto_name, $catatan, $donation_id, $user_id]);
                $_SESSION['success_message'] = 'Pengajuan donasi berhasil diperbarui!';
                header("Location: dashboard.php");
                exit();
            } catch (PDOException $e) {
                $error = 'Gagal memperbarui pengajuan: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-premium p-4 border-0">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="dashboard.php" class="btn btn-outline-primary btn-sm px-3">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <h3 class="fw-bold text-dark mb-0">Ubah Pengajuan Donasi</h3>
                </div>

                <?php if ($error): ?>
                    <script>alert("<?= addslashes($error) ?>");</script>
                <?php endif; ?>

                <form action="edit_donasi.php?id=<?= $donation_id ?>" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="judul_buku" class="form-label">Judul Buku</label>
                            <input type="text" class="form-control" id="judul_buku" name="judul_buku" required value="<?= htmlspecialchars($donasi['judul_buku']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="kategori_id" class="form-label">Kategori Buku</label>
                            <select class="form-select" id="kategori_id" name="kategori_id" required>
                                <option value="">Pilih Kategori...</option>
                                <?php foreach ($kategori_list as $kat): ?>
                                    <option value="<?= $kat['id'] ?>" <?= ($donasi['kategori_id'] == $kat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kondisi" class="form-label">Kondisi Fisik Buku</label>
                            <select class="form-select" id="kondisi" name="kondisi" required>
                                <option value="">Pilih Kondisi...</option>
                                <option value="sangat_baik" <?= ($donasi['kondisi'] === 'sangat_baik') ? 'selected' : '' ?>>Sangat Baik</option>
                                <option value="layak_baca" <?= ($donasi['kondisi'] === 'layak_baca') ? 'selected' : '' ?>>Layak Baca</option>
                                <option value="rusak_ringan" <?= ($donasi['kondisi'] === 'rusak_ringan') ? 'selected' : '' ?>>Rusak Ringan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jumlah" class="form-label">Jumlah Buku (Eks)</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required value="<?= htmlspecialchars($donasi['jumlah']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="foto" class="form-label">Ubah Foto Sampul / Kondisi Buku (Opsional)</label>
                        <div class="mb-2">
                            <span class="small text-muted d-block mb-1">Foto saat ini:</span>
                            <?php if ($donasi['foto'] && file_exists('../../assets/uploads/' . $donasi['foto'])): ?>
                                <img src="../../assets/uploads/<?= htmlspecialchars($donasi['foto']) ?>" width="120" class="img-thumbnail border" alt="">
                            <?php else: ?>
                                <span class="text-danger small">Foto tidak ditemukan</span>
                            <?php endif; ?>
                        </div>
                        <input type="file" class="form-control" id="foto" name="foto" accept=".png, .jpg, .jpeg">
                        <div class="form-text small text-muted">Biarkan kosong jika tidak ingin mengganti foto. Format: JPG, JPEG, PNG (Maks 2MB).</div>
                    </div>

                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan Tambahan (Opsional)</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tuliskan info pendukung..."><?= htmlspecialchars($donasi['catatan']) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 mt-3">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
