<?php
// views/pendonasi/tambah_donasi.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pendonasi') {
    header("Location: ../../login.php");
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
    $user_id = $_SESSION['user_id'];
    $judul_buku = trim($_POST['judul_buku']);
    $kategori_id = $_POST['kategori_id'];
    $kondisi = $_POST['kondisi'];
    $jumlah = intval($_POST['jumlah']);
    $catatan = trim($_POST['catatan']);
    
    // Upload file foto
    $foto_name = '';
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
            // Buat folder uploads jika belum ada
            $upload_dir = '../../assets/uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Nama unik untuk foto
            $foto_name = uniqid('buku_', true) . '.' . $file_ext;
            $upload_path = $upload_dir . $foto_name;
            
            if (!move_uploaded_file($file_tmp, $upload_path)) {
                $error = 'Gagal mengunggah foto buku!';
            }
        }
    } else {
        $error = 'Foto buku wajib diunggah!';
    }

    // Masukkan ke database jika tidak ada error
    if (empty($error)) {
        if (empty($judul_buku) || empty($kategori_id) || empty($kondisi) || $jumlah <= 0) {
            $error = 'Harap isi semua data dengan benar!';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO donasi (user_id, judul_buku, kategori_id, kondisi, jumlah, foto, catatan, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$user_id, $judul_buku, $kategori_id, $kondisi, $jumlah, $foto_name, $catatan]);
                $success = 'Pengajuan donasi berhasil dikirim! Silakan tunggu persetujuan Admin.';
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan pengajuan donasi: ' . $e->getMessage();
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
                    <h3 class="fw-bold text-dark mb-0">Donasikan Buku Baru</h3>
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

                <form action="tambah_donasi.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="judul_buku" class="form-label">Judul Buku</label>
                            <input type="text" class="form-control" id="judul_buku" name="judul_buku" required value="<?= isset($_POST['judul_buku']) ? htmlspecialchars($_POST['judul_buku']) : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="kategori_id" class="form-label">Kategori Buku</label>
                            <select class="form-select" id="kategori_id" name="kategori_id" required>
                                <option value="">Pilih Kategori...</option>
                                <?php foreach ($kategori_list as $kat): ?>
                                    <option value="<?= $kat['id'] ?>" <?= (isset($_POST['kategori_id']) && $_POST['kategori_id'] == $kat['id']) ? 'selected' : '' ?>>
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
                                <option value="sangat_baik" <?= (isset($_POST['kondisi']) && $_POST['kondisi'] == 'sangat_baik') ? 'selected' : '' ?>>Sangat Baik</option>
                                <option value="layak_baca" <?= (isset($_POST['kondisi']) && $_POST['kondisi'] == 'layak_baca') ? 'selected' : '' ?>>Layak Baca</option>
                                <option value="rusak_ringan" <?= (isset($_POST['kondisi']) && $_POST['kondisi'] == 'rusak_ringan') ? 'selected' : '' ?>>Rusak Ringan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jumlah" class="form-label">Jumlah Buku (Eks)</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required value="<?= isset($_POST['jumlah']) ? intval($_POST['jumlah']) : '1' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto Sampul / Kondisi Buku</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept=".png, .jpg, .jpeg" required>
                        <div class="form-text small text-muted">Format didukung: JPG, JPEG, PNG. Ukuran maksimal 2MB.</div>
                    </div>

                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan Tambahan (Opsional)</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tuliskan info pendukung, misal: bekas pelajaran kelas 9, terdapat sedikit coretan pensil, dll."><?= isset($_POST['catatan']) ? htmlspecialchars($_POST['catatan']) : '' ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 mt-3">Kirim Pengajuan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
