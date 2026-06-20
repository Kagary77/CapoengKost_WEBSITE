<?php
// ============================================================
//  ADMIN PANEL — Kost Putri Capoeng
//  Hanya bisa diakses oleh akun dengan role = 'admin'
// ============================================================
require_once 'config.php';

// Guard: harus login & admin — HARUS sebelum include header agar redirect bisa berjalan
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['login_error'] = 'Akses ditolak. Silakan login sebagai admin.';
    redirect('index.php');
}

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    redirect('index.php');
}

// ------------------------------------------------------------
//  Routing admin — tentukan halaman dahulu, SEBELUM ada output apa pun
// ------------------------------------------------------------
$page = $_GET['page'] ?? 'dashboard';
$valid_pages = ['dashboard', 'kamar', 'penghuni', 'komplain', 'transaksi', 'pesan', 'booking'];
if (!in_array($page, $valid_pages)) $page = 'dashboard';

$page_map = [
    'dashboard'  => ['file' => 'admin/page/dashboard.php',  'title' => 'Dashboard'],
    'kamar'      => ['file' => 'admin/page/kamar.php',      'title' => 'Kelola Kamar'],
    'penghuni'   => ['file' => 'admin/page/penghuni.php',   'title' => 'Data Penghuni'],
    'komplain'   => ['file' => 'admin/page/komplain.php',   'title' => 'Manajemen Komplain'],
    'transaksi'  => ['file' => 'admin/page/transaksi.php',  'title' => 'Manajemen Transaksi'],
    'pesan'      => ['file' => 'admin/page/pesan.php',      'title' => 'Manajemen Pesan'],
    'booking'    => ['file' => 'admin/page/booking.php',    'title' => 'Manajemen Booking'],
];

// ------------------------------------------------------------
//  PENTING: proses action (POST) setiap halaman dijalankan DI SINI,
//  sebelum admin/header.php mencetak HTML. Action boleh melakukan
//  redirect() dengan aman karena belum ada output sama sekali.
//  Inilah perbaikan utama dari struktur sebelumnya yang menyebabkan
//  error "headers already sent" saat melakukan aksi di admin panel.
// ------------------------------------------------------------
$action_file = "admin/actions/{$page}.php";
if (file_exists($action_file)) {
    require $action_file;
}

$pageTitle  = $page_map[$page]['title'];
$activePage = $page;

include 'admin/header.php';
include $page_map[$page]['file'];
include 'admin/footer.php';