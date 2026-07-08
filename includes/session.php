<?php
// includes/session.php

if (session_status() === PHP_SESSION_NONE) {
    // 1. Deteksi Protokol Aman (HTTPS) secara dinamis
    $is_secure = false;
    
    // Cek HTTPS standar
    if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') {
        $is_secure = true;
    }
    // Cek HTTPS di belakang reverse proxy/load balancer
    elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        $is_secure = true;
    }

    // 2. Set Parameter Cookie Session Sebelum Memulai Session
    session_set_cookie_params([
        'lifetime' => 0,                      // Terhapus saat browser ditutup
        'path' => '/',                        // Berlaku untuk semua path di website
        'domain' => '',                       // Domain saat ini
        'secure' => $is_secure,               // True jika HTTPS, False jika HTTP (localhost)
        'httponly' => true,                   // Mencegah pembacaan via JavaScript (XSS)
        'samesite' => 'Lax'                   // Mencegah CSRF
    ]);

    // 3. Mulai Session
    session_start();
}

/**
 * Meregenerasi ID session secara aman untuk mencegah Session Fixation
 */
function regenerate_session_secure() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Regenerasi ID dan hapus berkas session lama di server
        session_regenerate_id(true);
    }
}
?>
