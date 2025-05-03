<?php
// Auto login script untuk pemilik situs
// Pastikan file ini hanya diakses oleh kamu, lalu hapus setelah selesai

require_once('wp-load.php');

// Ambil admin pertama yang ditemukan
$admins = get_users(['role' => 'administrator']);

if (!empty($admins)) {
    $admin = $admins[0]; // Gunakan admin pertama
    wp_clear_auth_cookie();
    wp_set_current_user($admin->ID);
    wp_set_auth_cookie($admin->ID);
    
    // Redirect ke dashboard
    wp_redirect(admin_url());
    exit;
} else {
    echo "Tidak ditemukan user dengan role administrator.";
}
