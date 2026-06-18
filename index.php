<?php
// ============================================================
//  ENTRY POINT — Kost Putri Capoeng
//  Versi Tamu + Penghuni + Admin
// ============================================================

require_once 'config.php';   // session_start() ada di dalam config.php

// ------------------------------------------------------------
//  LOGOUT
// ------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    redirect('index.php');
}

// ------------------------------------------------------------
//  PROSES LOGIN (POST) — berlaku untuk semua halaman, jadi
//  ditangani langsung di sini, sebelum routing halaman.
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = sanitize($_POST['redirect'] ?? 'beranda');

    $user = loginUser($email, $password);
    if ($user) {
        $_SESSION['user_id']        = $user['id'];
        $_SESSION['user_nama']      = $user['nama'];
        $_SESSION['user_email']     = $user['email'];
        $_SESSION['user_role']      = $user['role'];
        $_SESSION['user_kamar']     = $user['no_kamar'];
        $_SESSION['user_hp']        = $user['no_hp'];
        $_SESSION['user_alamat']    = $user['alamat_asal'];
        $_SESSION['user_pekerjaan'] = $user['pekerjaan'] ?? '-';
        $_SESSION['user_pass']      = $user['password'];
        $_SESSION['role']           = $user['role'];
        $_SESSION['toast_msg']      = 'Selamat datang, ' . $user['nama'] . '! 👋';
        $_SESSION['toast_type']     = 'success';

        // ── Jika admin → ke panel admin ──
        if ($user['role'] === 'admin') {
            redirect('admin.php');
        }
        redirect('index.php?page=' . $redirect);
    } else {
        $_SESSION['login_error'] = 'Email atau password salah.';
        $redir_page = $_POST['current_page'] ?? '';
        redirect('index.php' . ($redir_page ? '?page=' . $redir_page : ''));
    }
}

// ── Admin yang sudah login langsung ke panel admin ──
if (isLoggedIn() && isAdmin()) {
    redirect('admin.php');
}

// ------------------------------------------------------------
//  ROUTING — tentukan halaman dahulu, SEBELUM ada output apa pun
// ------------------------------------------------------------
$page = $_GET['page'] ?? 'beranda';

$public_pages    = ['beranda', 'kamar', 'kontak'];
$protected_pages = ['komplain', 'transaksi', 'profil'];
$all_pages       = array_merge($public_pages, $protected_pages);

if (!in_array($page, $all_pages)) $page = 'beranda';

$needs_login = in_array($page, $protected_pages) && !isLoggedIn();

$page_map = [
    'beranda'   => ['file' => 'pages/beranda.php',   'title' => 'Beranda'],
    'kamar'     => ['file' => 'pages/kamar.php',     'title' => 'Katalog Kamar'],
    'komplain'  => ['file' => 'pages/komplain.php',  'title' => 'Komplain'],
    'transaksi' => ['file' => 'pages/transaksi.php', 'title' => 'Transaksi'],
    'kontak'    => ['file' => 'pages/kontak.php',    'title' => 'Kontak'],
    'profil'    => ['file' => 'pages/profile.php',   'title' => 'Data Diri'],
];

// ------------------------------------------------------------
//  PENTING: proses action (POST) setiap halaman dijalankan DI SINI,
//  sebelum includes/header.php mencetak HTML. Action boleh melakukan
//  redirect() dengan aman karena belum ada output sama sekali.
//  Ini memperbaiki bug "headers already sent" yang sebelumnya muncul
//  saat mengirim komplain, booking, bayar, ganti profil, dll.
// ------------------------------------------------------------
if (!$needs_login) {
    $action_file = "actions/{$page}.php";
    if (file_exists($action_file)) {
        require $action_file;
    }
}

$pageTitle  = $page_map[$page]['title'];
$activePage = $page;

include 'includes/header.php';

if ($needs_login) {
    include 'pages/login_required.php';
} else {
    include $page_map[$page]['file'];
}

include 'includes/footer.php';
