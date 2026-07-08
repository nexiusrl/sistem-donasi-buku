<?php
// views/admin/kategori.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['error_message']);
unset($_SESSION['success_message']);


// Proses tambah kategori baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kategori'])) {
    $nama_kategori = trim($_POST['nama_kategori']);

    if (empty($nama_kategori)) {
        $error = 'Nama kategori tidak boleh kosong!';
    } elseif (strlen($nama_kategori) > 50) {
        $error = 'Nama kategori maksimal 50 karakter!';
    } else {
        try {
            // Cek jika sudah ada
            $stmt = $pdo->prepare("SELECT id FROM kategori_buku WHERE nama_kategori = ?");
            $stmt->execute([$nama_kategori]);
            if ($stmt->fetch()) {
                $error = 'Kategori "' . htmlspecialchars($nama_kategori) . '" sudah terdaftar!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO kategori_buku (nama_kategori) VALUES (?)");
                $stmt->execute([$nama_kategori]);
                $success = 'Kategori baru berhasil ditambahkan!';
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan kategori: ' . $e->getMessage();
        }
    }
}

// Ambil seluruh daftar kategori
try {
    // Ambil kategori beserta jumlah buku yang menggunakan kategori tersebut untuk info di UI
    $stmt = $pdo->query("
        SELECT k.id, k.nama_kategori, COUNT(d.id) as total_buku
        FROM kategori_buku k
        LEFT JOIN donasi d ON k.id = d.kategori_id
        GROUP BY k.id
        ORDER BY k.nama_kategori ASC
    ");
    $kategori_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $kategori_list = [];
}
?>

<div class="container py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="dashboard.php" class="btn btn-outline-primary btn-sm px-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
        <h3 class="fw-bold text-dark mb-0">Manajemen Kategori Buku</h3>
    </div>

    <?php if ($error): ?>
        <script>alert("<?= addslashes($error) ?>");</script>
    <?php endif; ?>

    <?php if ($success): ?>
        <script>alert("<?= addslashes($success) ?>");</script>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Form Tambah Kategori -->
        <div class="col-lg-4">
            <div class="card card-premium p-4 border-0">
                <h5 class="fw-bold text-dark mb-3">Tambah Kategori Baru</h5>
                <form action="kategori.php" method="POST">
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" placeholder="Contoh: Komik, Novel Sejarah" required>
                    </div>
                    <button type="submit" name="tambah_kategori" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar Kategori -->
        <div class="col-lg-8">
            <div class="card card-premium p-4 border-0">
                <h5 class="fw-bold text-dark mb-3">Daftar Kategori Aktif</h5>
                
                <?php if (empty($kategori_list)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-tag fs-2 d-block mb-2"></i>
                        Belum ada kategori buku yang terdaftar.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kategori</th>
                                    <th>Buku Terkait</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($kategori_list as $kat): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($kat['nama_kategori']) ?></td>
                                        <td>
                                            <span class="badge bg-teal-light text-dark border-1 px-3 py-2">
                                                <?= $kat['total_buku'] ?> Buku
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="edit_kategori.php?id=<?= $kat['id'] ?>" class="btn btn-warning btn-sm text-white px-3 fw-semibold">
                                                    <i class="bi bi-pencil-fill me-1"></i> Edit
                                                </a>
                                                <a href="hapus_kategori.php?id=<?= $kat['id'] ?>" class="btn btn-danger btn-sm px-3 fw-semibold" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                                    <i class="bi bi-trash-fill me-1"></i> Hapus
                                                </a>
                                            </div>
                                        </td>
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
