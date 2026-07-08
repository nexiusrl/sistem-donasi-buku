<?php
// views/admin/users.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$error = '';
$success = '';

// Proses hapus pengguna
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    if ($delete_id === intval($_SESSION['user_id'])) {
        $error = 'Anda tidak dapat menghapus akun Anda sendiri!';
    } else {
        try {
            // Cek apakah user target ada dan perannya apa
            $stmt = $pdo->prepare("SELECT role, nama FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            $target_user = $stmt->fetch();
            
            if (!$target_user) {
                $error = 'Pengguna tidak ditemukan!';
            } elseif ($target_user['role'] === 'admin') {
                $error = 'Akun Administrator tidak dapat dihapus!';
            } else {
                // Hapus pengguna
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$delete_id]);
                $success = 'Akun pengguna "' . htmlspecialchars($target_user['nama']) . '" berhasil dihapus!';
            }
        } catch (PDOException $e) {
            $error = 'Gagal menghapus pengguna: ' . $e->getMessage();
        }
    }
}

// Ambil seluruh daftar pengguna
try {
    // Ambil daftar pengguna beserta statistik total kontribusi donasi buku mereka
    $stmt = $pdo->query("
        SELECT u.id, u.nama, u.email, u.no_telp, u.role, u.created_at,
               COALESCE((SELECT SUM(jumlah) FROM donasi WHERE user_id = u.id AND status = 'diterima'), 0) as total_kontribusi
        FROM users u
        ORDER BY u.role ASC, u.nama ASC
    ");
    $users_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $users_list = [];
}
?>

<div class="container py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="dashboard.php" class="btn btn-outline-primary btn-sm px-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
        <h3 class="fw-bold text-dark mb-0">Manajemen Pengguna</h3>
    </div>

    <?php if ($error): ?>
        <script>alert("<?= addslashes($error) ?>");</script>
    <?php endif; ?>

    <?php if ($success): ?>
        <script>alert("<?= addslashes($success) ?>");</script>
    <?php endif; ?>

    <!-- Tabel Daftar Pengguna -->
    <div class="card card-premium p-4 border-0">
        <h5 class="fw-bold text-dark mb-3">Daftar Akun Pengguna Terdaftar</h5>
        
        <?php if (empty($users_list)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-people fs-2 d-block mb-2"></i>
                Belum ada pengguna terdaftar.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-premium">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No Telepon</th>
                            <th>Peran (Role)</th>
                            <th>Kontribusi (Buku Diterima)</th>
                            <th>Tanggal Bergabung</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users_list as $usr): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($usr['nama']) ?></span>
                                    <span class="text-muted small">ID Pengguna: <?= $usr['id'] ?></span>
                                </td>
                                <td><?= htmlspecialchars($usr['email']) ?></td>
                                <td><?= htmlspecialchars($usr['no_telp'] ?: '-') ?></td>
                                <td>
                                    <?php if ($usr['role'] === 'admin'): ?>
                                        <span class="badge bg-danger text-white px-3 py-2">Administrator</span>
                                    <?php else: ?>
                                        <span class="badge bg-teal-light text-dark px-3 py-2">Pendonasi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= number_format($usr['total_kontribusi']) ?> Eks</span>
                                </td>
                                <td><?= date('d M Y', strtotime($usr['created_at'])) ?></td>
                                <td class="text-center">
                                    <?php if ($usr['role'] !== 'admin' && intval($usr['id']) !== intval($_SESSION['user_id'])): ?>
                                        <a href="users.php?delete_id=<?= $usr['id'] ?>" class="btn btn-danger btn-sm px-3 fw-semibold" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini? Seluruh riwayat donasi pengguna ini juga akan terhapus.')">
                                            <i class="bi bi-trash-fill me-1"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
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
