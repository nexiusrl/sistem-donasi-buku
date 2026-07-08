<?php
// views/pendonasi/profil.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pendonasi') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data user saat ini
try {
    $stmt = $pdo->prepare("SELECT nama, email, no_telp FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = null;
}

if (!$user) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $no_telp = trim($_POST['no_telp']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (empty($nama) || empty($no_telp)) {
        $error = 'Nama dan Nomor Telepon wajib diisi!';
    } elseif (strlen($nama) < 3) {
        $error = 'Nama minimal harus 3 karakter!';
    } elseif (!preg_match("/^[a-zA-Z\s\.\']+$/", $nama)) {
        $error = 'Nama hanya boleh berisi huruf, spasi, titik, atau tanda petik!';
    } elseif (!preg_match("/^[0-9]{10,15}$/", $no_telp)) {
        $error = 'Nomor telepon harus berupa angka antara 10 sampai 15 digit!';
    } elseif (!empty($password_baru)) {
        if (strlen($password_baru) < 5) {
            $error = 'Kata sandi baru minimal harus 5 karakter!';
        } elseif ($password_baru !== $konfirmasi_password) {
            $error = 'Konfirmasi kata sandi baru tidak cocok!';
        }
    }

    if (empty($error)) {
        try {
            if (!empty($password_baru)) {
                // Update dengan password baru
                $hashed_password = password_hash($password_baru, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, no_telp = ?, password = ? WHERE id = ?");
                $stmt->execute([$nama, $no_telp, $hashed_password, $user_id]);
            } else {
                // Update profil saja
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, no_telp = ? WHERE id = ?");
                $stmt->execute([$nama, $no_telp, $user_id]);
            }

            // Update nama session agar langsung terefleksi di navbar
            $_SESSION['nama'] = $nama;
            $success = 'Profil Anda berhasil diperbarui!';
            
            // Refresh data user
            $user['nama'] = $nama;
            $user['no_telp'] = $no_telp;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan perubahan: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card card-premium p-4 border-0">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="dashboard.php" class="btn btn-outline-primary btn-sm px-3">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                    <h3 class="fw-bold text-dark mb-0">Ubah Profil Saya</h3>
                </div>

                <?php if ($error): ?>
                    <script>alert("<?= addslashes($error) ?>");</script>
                <?php endif; ?>

                <?php if ($success): ?>
                    <script>alert("<?= addslashes($success) ?>");</script>
                <?php endif; ?>

                <form action="profil.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email (Tidak Dapat Diubah)</label>
                        <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="no_telp" class="form-label">Nomor Telepon/WA</label>
                        <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?= htmlspecialchars($user['no_telp']) ?>" required>
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-bold text-dark mb-3">Ubah Kata Sandi (Kosongkan jika tidak diganti)</h5>

                    <div class="mb-3">
                        <label for="password_baru" class="form-label">Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="password_baru" name="password_baru">
                    </div>

                    <div class="mb-3">
                        <label for="konfirmasi_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 mt-3">
                        <i class="bi bi-check-lg me-1"></i> Simpan Profil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
