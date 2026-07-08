<?php
// views/pendonasi/hapus_donasi.php
require_once '../../config/database.php';
require_once '../../includes/session.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pendonasi') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$donation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($donation_id > 0) {
    try {
        // Ambil info donasi & foto sebelum dihapus
        $stmt = $pdo->prepare("SELECT foto, status FROM donasi WHERE id = ? AND user_id = ?");
        $stmt->execute([$donation_id, $user_id]);
        $donasi = $stmt->fetch();

        if ($donasi) {
            if ($donasi['status'] === 'pending') {
                // Hapus foto fisik dari server
                if ($donasi['foto']) {
                    $file_path = '../../assets/uploads/' . $donasi['foto'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                // Hapus dari database
                $stmt = $pdo->prepare("DELETE FROM donasi WHERE id = ? AND user_id = ?");
                $stmt->execute([$donation_id, $user_id]);
                
                $_SESSION['success_message'] = 'Pengajuan donasi berhasil dibatalkan!';
            } else {
                $_SESSION['error_message'] = 'Pengajuan donasi yang sudah diproses tidak dapat dibatalkan!';
            }
        } else {
            $_SESSION['error_message'] = 'Data donasi tidak ditemukan!';
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Gagal menghapus pengajuan donasi: ' . $e->getMessage();
    }
}

header("Location: dashboard.php");
exit();
