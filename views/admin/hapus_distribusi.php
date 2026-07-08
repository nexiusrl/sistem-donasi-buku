<?php
// views/admin/hapus_distribusi.php
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
        $stmt = $pdo->prepare("DELETE FROM distribusi WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_message'] = 'Log penyaluran buku berhasil dihapus!';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Gagal menghapus log penyaluran: ' . $e->getMessage();
    }
}

header("Location: distribusi.php");
exit();
