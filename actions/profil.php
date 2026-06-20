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
        // Upload foto KTP jika ada
        $foto_ktp = null;
        if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === UPLOAD_ERR_OK) {
            $foto_ktp = uploadFotoKTP($_FILES['foto_ktp']);
            if (!$foto_ktp) {
                $_SESSION['toast_msg']  = 'Gagal upload foto KTP. Pastikan file gambar (max 5MB).';
                $_SESSION['toast_type'] = 'error';
                redirect('index.php?page=profil');
                exit;
            }
        }

        updateUserProfile($_SESSION['user_id'] ?? 0, $nama, $nohp, $alamat, $pekerjaan, $foto_ktp);
        $_SESSION['toast_msg']  = 'Data diri berhasil diperbarui! ✅';
        $_SESSION['toast_type'] = 'success';
        redirect('index.php?page=profil');
    }
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
}