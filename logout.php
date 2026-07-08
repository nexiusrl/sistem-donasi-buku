<?php
// logout.php
require_once 'includes/session.php';
session_unset();
session_destroy();

// Hapus cookie PHPSESSID di browser secara paksa
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
header("Location: index.php");
exit();
?>
