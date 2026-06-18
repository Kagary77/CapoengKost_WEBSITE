<?php
// ============================================================
//  ACTION: Data Diri / Profil (Penghuni)
//  Logika proses (POST) — dijalankan sebelum HTML dicetak.
// ============================================================

// Handle update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profil') {
    $nama      = sanitize($_POST['nama'] ?? '');
    $nohp      = sanitize($_POST['no_hp'] ?? '');
    $alamat    = sanitize($_POST['alamat'] ?? '');
    $pekerjaan = sanitize($_POST['pekerjaan'] ?? '');

    if ($nama) {
        updateUserProfile($_SESSION['user_id'] ?? 0, $nama, $nohp, $alamat, $pekerjaan);
        $_SESSION['toast_msg']  = 'Data diri berhasil diperbarui! ✅';
        $_SESSION['toast_type'] = 'success';
        redirect('index.php?page=profil');
    }
    // Dibaca oleh view (pages/profile.php) jika validasi gagal
    $error = 'Nama tidak boleh kosong.';
}

// Handle ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ganti_password') {
    $old = md5($_POST['old_pass'] ?? '');
    $new = $_POST['new_pass'] ?? '';
    $cfm = $_POST['confirm_pass'] ?? '';

    if ($old !== ($_SESSION['user_pass'] ?? '')) {
        $pass_error = 'Password lama tidak sesuai.';
    } elseif (strlen($new) < 6) {
        $pass_error = 'Password baru minimal 6 karakter.';
    } elseif ($new !== $cfm) {
        $pass_error = 'Konfirmasi password tidak cocok.';
    } else {
        updateUserPassword($_SESSION['user_id'] ?? 0, $new);
        $_SESSION['toast_msg']  = 'Password berhasil diubah! 🔒';
        $_SESSION['toast_type'] = 'success';
        redirect('index.php?page=profil');
    }
    // $pass_error (jika ada) dibaca oleh view (pages/profile.php)
}
