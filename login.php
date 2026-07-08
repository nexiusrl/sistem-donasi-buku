<?php
// login.php
require_once 'config/database.php';

require_once 'includes/session.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: views/admin/dashboard.php");
    } else {
        header("Location: views/pendonasi/dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerasi session ID untuk keamanan
                regenerate_session_secure();

                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                // Redirect berdasarkan role
                if ($user['role'] === 'admin') {
                    header("Location: views/admin/dashboard.php");
                } else {
                    header("Location: views/pendonasi/dashboard.php");
                }
                exit();
            } else {
                $error = 'Email atau kata sandi salah!';
            }
        } catch (PDOException $e) {
            $error = 'Gagal memproses data: ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container-fluid px-0 py-0" style="margin-top: -1.5rem; min-height: calc(100vh - 140px); display: flex;">
    <div class="row g-0 w-100">
        <!-- Left Side: Editorial Quote (hidden on mobile) -->
        <div class="col-lg-6 d-none d-lg-flex bg-white flex-column justify-content-between p-5 border-end border-3 border-dark" style="background-image: radial-gradient(var(--color-cream) 1px, transparent 1px); background-size: 20px 20px;">
            <div class="p-3">
                <span class="badge border border-dark text-dark px-3 py-1">Koleksi & Berbagi</span>
            </div>
            <div class="p-3 my-auto">
                <h1 class="display-5 fw-bold text-dark mb-4 leading-tight">“Buku yang berdebu di rak Anda adalah petualangan yang terhenti. Berikan ia kehidupan baru.”</h1>
                <p class="text-muted text-uppercase tracking-wider small">- BUKUBERBAGI KOMUNITAS</p>
            </div>
            <div class="p-3 border-top border-dark border-1 d-flex justify-content-between text-muted small">
                <span>Versi 1.0</span>
                <span>Inisiasi Kurasi Buku Fisik</span>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="col-lg-6 col-12 d-flex align-items-center justify-content-center p-4 p-md-5">
            <div class="w-100" style="max-width: 440px;">
                <div class="card card-premium p-4 border-0">
                    <div class="mb-4">
                        <span class="text-uppercase small fw-bold text-muted" style="color: var(--color-gold) !important; letter-spacing: 0.1em;">Pintu Masuk Portal</span>
                        <h3 class="fw-bold text-dark mt-1">Masuk Pengguna</h3>
                        <p class="text-muted small">Akses dashboard pendonasi atau admin Anda.</p>
                    </div>

                    <?php if ($error): ?>
                        <script>alert("<?= addslashes($error) ?>");</script>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Alamat Email</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Kata Sandi</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Masuk Ke Dashboard</button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="small text-muted">Belum terdaftar? <a href="register.php" class="text-dark fw-bold">Daftar Akun Baru</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
