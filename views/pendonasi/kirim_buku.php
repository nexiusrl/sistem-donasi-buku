<?php
// views/pendonasi/kirim_buku.php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pendonasi') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$donation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verifikasi Donasi
try {
    $stmt = $pdo->prepare("SELECT * FROM donasi WHERE id = ? AND user_id = ?");
    $stmt->execute([$donation_id, $user_id]);
    $donasi = $stmt->fetch();
} catch (PDOException $e) {
    $donasi = null;
}

if (!$donasi) {
    header("Location: dashboard.php");
    exit();
}

// Donasi harus berstatus disetujui untuk dikirim
if ($donasi['status'] !== 'disetujui') {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode_pengiriman = $_POST['metode_pengiriman'];
    $ekspedisi = isset($_POST['ekspedisi']) ? trim($_POST['ekspedisi']) : '';
    $nomor_resi = isset($_POST['nomor_resi']) ? trim($_POST['nomor_resi']) : '';

    if (empty($metode_pengiriman)) {
        $error = 'Pilih metode pengiriman!';
    } elseif ($metode_pengiriman === 'kurir' && (empty($ekspedisi) || empty($nomor_resi))) {
        $error = 'Nama ekspedisi dan nomor resi wajib diisi jika mengirim via kurir!';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE donasi 
                SET status = 'dikirim', metode_pengiriman = ?, ekspedisi = ?, nomor_resi = ? 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $metode_pengiriman,
                $metode_pengiriman === 'kurir' ? $ekspedisi : null,
                $metode_pengiriman === 'kurir' ? $nomor_resi : null,
                $donation_id,
                $user_id
            ]);
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan status pengiriman: ' . $e->getMessage();
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
                        <i class="bi bi-arrow-left"></i> Batal
                    </a>
                    <h4 class="fw-bold text-dark mb-0">Konfirmasi Pengiriman</h4>
                </div>

                <div class="alert alert-info py-2 small" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Buku: <strong><?= htmlspecialchars($donasi['judul_buku']) ?></strong> (<?= htmlspecialchars($donasi['jumlah']) ?> Eks)
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="kirim_buku.php?id=<?= $donation_id ?>" method="POST" id="form-kirim">
                    <div class="mb-3">
                        <label for="metode_pengiriman" class="form-label">Metode Pengiriman</label>
                        <select class="form-select" id="metode_pengiriman" name="metode_pengiriman" required>
                            <option value="">Pilih Metode...</option>
                            <option value="kurir">Menggunakan Ekspedisi / Kurir</option>
                            <option value="dropoff">Mengantarkan Langsung (Drop-off)</option>
                            <option value="cod">COD (Ketemuan Langsung dengan Pengelola)</option>
                        </select>
                    </div>

                    <!-- Input Ekspedisi & Resi (Hanya tampil jika kurir dipilih) -->
                    <div id="kurir-fields" class="d-none">
                        <div class="mb-3">
                            <label for="ekspedisi" class="form-label">Nama Ekspedisi (Kurir)</label>
                            <input type="text" class="form-control" id="ekspedisi" name="ekspedisi" placeholder="Contoh: JNE, J&T, SiCepat, GoSend">
                        </div>
                        <div class="mb-3">
                            <label for="nomor_resi" class="form-label">Nomor Resi / Bukti Pengiriman</label>
                            <input type="text" class="form-control" id="nomor_resi" name="nomor_resi" placeholder="Masukkan nomor resi atau nomor pesanan ojek online">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 mt-3">Konfirmasi Sudah Dikirim</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectMetode = document.getElementById('metode_pengiriman');
    const kurirFields = document.getElementById('kurir-fields');
    const inputEkspedisi = document.getElementById('ekspedisi');
    const inputResi = document.getElementById('nomor_resi');

    selectMetode.addEventListener('change', function() {
        if (this.value === 'kurir') {
            kurirFields.classList.remove('d-none');
            inputEkspedisi.setAttribute('required', 'required');
            inputResi.setAttribute('required', 'required');
        } else {
            kurirFields.classList.add('d-none');
            inputEkspedisi.removeAttribute('required');
            inputResi.removeAttribute('required');
            inputEkspedisi.value = '';
            inputResi.value = '';
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
