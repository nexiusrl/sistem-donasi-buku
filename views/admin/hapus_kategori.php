<?php
// views/admin/hapus_kategori.php
require_once '../../config/database.php';
require_once '../../includes/session.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        // Cek jika kategori masih digunakan oleh donasi buku (RESTRICT check)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM donasi WHERE kategori_id = ?");
        $stmt->execute([$id]);
        $total_buku = $stmt->fetchColumn();

        if ($total_buku > 0) {
            $_SESSION['error_message'] = 'Kategori tidak dapat dihapus karena masih digunakan oleh ' . $total_buku . ' data buku!';
        } else {
            $stmt = $pdo->prepare("DELETE FROM kategori_buku WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success_message'] = 'Kategori berhasil dihapus!';
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Gagal menghapus kategori: ' . $e->getMessage();
    }
}

header("Location: kategori.php");
exit();
