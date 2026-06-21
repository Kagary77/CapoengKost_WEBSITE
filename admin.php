<?php
/**
 * ================================================================
 *  ADMIN PANEL — Kost Putri Capoeng
 *  Version   : 2.0 (Alur Transaksi Baru: Terkirim → Pending → Lunas/Gagal)
 *  Author    : [Nama Anda / Tim Developer]
 *  Deskripsi : Hanya bisa diakses oleh akun dengan role 'admin'.
 *              Bertindak sebagai Front Controller untuk semua halaman admin.
 *              Memisahkan logika (Action) dan tampilan (Page) secara jelas.
 * ================================================================
 */

require_once 'config.php';

// =================================================================
//  1. GUARD AKSES
//  - Cek apakah user sudah login.
//  - Cek apakah role user adalah 'admin'.
//  - Jika gagal, simpan pesan error dan redirect ke halaman login.
// =================================================================
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['login_error'] = 'Akses ditolak. Silakan login sebagai admin.';
    redirect('index.php');
}

// =================================================================
//  2. PROSES LOGOUT
//  - Jika parameter 'action=logout' ditemukan, hancurkan session.
// =================================================================
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    redirect('index.php');
}

// =================================================================
//  3. ROUTING (Penentuan Halaman)
//  - Ambil parameter 'page' dari URL. Default: 'dashboard'.
//  - Validasi: hanya halaman yang terdaftar di $valid_pages yang diizinkan.
// =================================================================
$page = $_GET['page'] ?? 'dashboard';
$valid_pages = ['dashboard', 'kamar', 'penghuni', 'komplain', 'transaksi', 'pesan', 'booking'];
if (!in_array($page, $valid_pages)) $page = 'dashboard';

/**
 * Daftar pemetaan halaman ke file view dan judul.
 * - 'file'  : Lokasi file tampilan (admin/page/...)
 * - 'title' : Judul yang akan ditampilkan di header dan topbar.
 */
$page_map = [
    'dashboard'  => ['file' => 'admin/page/dashboard.php',  'title' => 'Dashboard'],
    'kamar'      => ['file' => 'admin/page/kamar.php',      'title' => 'Kelola Kamar'],
    'penghuni'   => ['file' => 'admin/page/penghuni.php',   'title' => 'Data Penghuni'],
    'komplain'   => ['file' => 'admin/page/komplain.php',   'title' => 'Manajemen Komplain'],
    'transaksi'  => ['file' => 'admin/page/transaksi.php',  'title' => 'Manajemen Transaksi'],
    'pesan'      => ['file' => 'admin/page/pesan.php',      'title' => 'Manajemen Pesan'],
    'booking'    => ['file' => 'admin/page/booking.php',    'title' => 'Manajemen Booking'],
];

// =================================================================
//  4. EKSEKUSI LOGIKA (ACTION)
//  - File action di-load SEBELUM output HTML (header) dicetak.
//  - Ini memastikan bahwa proses POST (seperti tambah/edit/hapus) dapat
//    melakukan redirect() tanpa error "headers already sent".
//  - Ini adalah perbaikan struktur yang krusial untuk stabilitas sistem.
// =================================================================
$action_file = "admin/actions/{$page}.php";
if (file_exists($action_file)) {
    require $action_file;
}

// =================================================================
//  5. TAMPILAN (VIEW)
//  - Set variabel untuk header (judul halaman & halaman aktif).
//  - Load header, page, dan footer secara berurutan.
// =================================================================
$pageTitle  = $page_map[$page]['title'];
$activePage = $page;

include 'admin/header.php';      // Sidebar, Topbar, dan CSS/JS global
include $page_map[$page]['file']; // Konten utama sesuai halaman
include 'admin/footer.php';       // Script penutup dan modal global