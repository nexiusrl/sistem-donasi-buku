<?php
// register.php
require_once 'config/database.php';
require_once 'includes/header.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $no_telp = trim($_POST['no_telp']);

    if (empty($nama) || empty($email) || empty($password) || empty($no_telp)) {
        $error = 'Semua field wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek jika email sudah terdaftar
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Simpan ke database
            try {
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, no_telp, role) VALUES (?, ?, ?, ?, 'pendonasi')");
                $stmt->execute([$nama, $email, $hashed_password, $no_telp]);
                $success = 'Pendaftaran berhasil! Silakan masuk ke akun Anda.';
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan data: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="container-fluid px-0 py-0" style="margin-top: -1.5rem; min-height: calc(100vh - 140px); display: flex;">
    <div class="row g-0 w-100">
        <!-- Left Side: Editorial Quote (hidden on mobile) -->
        <div class="col-lg-6 d-none d-lg-flex bg-white flex-column justify-content-between p-5 border-end border-3 border-dark" style="background-image: radial-gradient(var(--color-cream) 1px, transparent 1px); background-size: 20px 20px;">
            <div class="p-3">
                <span class="badge border border-dark text-dark px-3 py-1">Registrasi Donatur</span>
            </div>
            <div class="p-3 my-auto">
                <h1 class="display-5 fw-bold text-dark mb-4 leading-tight">“Tindakan kecil mendonasikan satu buku bisa membuka ribuan jalan bagi pikiran yang ingin bertumbuh.”</h1>
                <p class="text-muted text-uppercase tracking-wider small">- BUKUBERBAGI FILOSOFI</p>
            </div>
            <div class="p-3 border-top border-dark border-1 d-flex justify-content-between text-muted small">
                <span>Versi 1.0</span>
                <span>Inisiasi Kurasi Buku Fisik</span>
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="col-lg-6 col-12 d-flex align-items-center justify-content-center p-4 p-md-5">
            <div class="w-100" style="max-width: 460px;">
                <div class="card card-premium p-4 border-0">
                    <div class="mb-4">
                        <span class="text-uppercase small fw-bold text-muted" style="color: var(--color-gold) !important; letter-spacing: 0.1em;">Bergabung Sebagai Pendonasi</span>
                        <h3 class="fw-bold text-dark mt-1">Daftar Akun Baru</h3>
                        <p class="text-muted small">Mulai perjalanan donasi buku fisik Anda hari ini.</p>
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

                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" required value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Alamat Email</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="no_telp" class="form-label">Nomor Telepon/WA</label>
                            <input type="text" class="form-control" id="no_telp" name="no_telp" placeholder="Contoh: 0812XXXXXXXX" required value="<?= isset($_POST['no_telp']) ? htmlspecialchars($_POST['no_telp']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Kata Sandi</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Daftar Sekarang</button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="small text-muted">Sudah terdaftar? <a href="login.php" class="text-dark fw-bold">Masuk di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
