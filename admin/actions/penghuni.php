<?php
/**
 * ================================================================
 *  ACTION: PENGHUNI (ADMIN)
 *  - Menangani CRUD data penghuni (Tambah, Reset Password, Hapus).
 *  - Status kamar otomatis berubah saat penghuni ditambahkan/dihapus.
 *  - Password menggunakan password_hash() untuk keamanan.
 * ================================================================
 */

// =================================================================
//  AKSI 1: TAMBAH PENGHUNI BARU
//  - Validasi: cek duplikat email dan nomor kamar.
//  - Status kamar otomatis menjadi 'terisi'.
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah_penghuni') {
    $db        = getDB();
    $nama      = sanitize($_POST['nama'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? 'penghuni123';
    $no_kamar  = sanitize($_POST['no_kamar'] ?? '');
    $no_hp     = sanitize($_POST['no_hp'] ?? '');
    $alamat    = sanitize($_POST['alamat'] ?? '');
    $pekerjaan = sanitize($_POST['pekerjaan'] ?? 'Mahasiswa');

    if ($nama && $email && $no_kamar) {
        if ($db) {
            // Cek duplikat kamar
            $stmt = $db->prepare("SELECT id FROM tb_users WHERE no_kamar = ?");
            $stmt->bind_param('s', $no_kamar);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $_SESSION['toast_msg']  = 'Kamar ' . $no_kamar . ' sudah ditempati penghuni lain.';
                $_SESSION['toast_type'] = 'error';
                redirect('admin.php?page=penghuni');
                exit;
            }
            // Cek duplikat email
            $stmt2 = $db->prepare("SELECT id FROM tb_users WHERE email = ?");
            $stmt2->bind_param('s', $email);
            $stmt2->execute();
            if ($stmt2->get_result()->num_rows > 0) {
                $_SESSION['toast_msg']  = 'Email sudah terdaftar.';
                $_SESSION['toast_type'] = 'error';
                redirect('admin.php?page=penghuni');
                exit;
            }
        }

        // Hash password dengan bcrypt
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        if ($db) {
            $stmt = $db->prepare(
                "INSERT INTO tb_users (nama, email, password, role, no_kamar, no_hp, alamat_asal, pekerjaan)
                 VALUES (?, ?, ?, 'penghuni', ?, ?, ?, ?)"
            );
            $stmt->bind_param('sssssss', $nama, $email, $hashed, $no_kamar, $no_hp, $alamat, $pekerjaan);
            $stmt->execute();

            // Update status kamar menjadi terisi
            $stmt2 = $db->prepare("UPDATE tb_kamar SET status = 'terisi' WHERE nama = ?");
            $stmt2->bind_param('s', $no_kamar);
            $stmt2->execute();
        }
        $_SESSION['toast_msg']  = 'Penghuni baru berhasil ditambahkan. Status kamar otomatis menjadi Terisi.';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_msg']  = 'Harap isi semua field yang wajib.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('admin.php?page=penghuni');
}

// =================================================================
//  AKSI 2: RESET PASSWORD
//  - Reset password penghuni ke 'penghuni123' (hash bcrypt).
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset_password') {
    $db  = getDB();
    $id  = (int)($_POST['id'] ?? 0);
    $new = 'penghuni123';
    if ($id && $db) {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE tb_users SET password=? WHERE id=?");
        $stmt->bind_param('si', $hashed, $id);
        $stmt->execute();
    }
    $_SESSION['toast_msg']  = 'Password direset ke "penghuni123".';
    $_SESSION['toast_type'] = 'success';
    redirect('admin.php?page=penghuni');
}

// =================================================================
//  AKSI 3: HAPUS PENGHUNI
//  - Hapus data penghuni.
//  - Status kamar otomatis kembali menjadi 'tersedia'.
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus_penghuni') {
    $db = getDB();
    $id = (int)($_POST['id'] ?? 0);
    if ($id && $db) {
        $stmt = $db->prepare("SELECT no_kamar FROM tb_users WHERE id=? AND role='penghuni'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $no_kamar = $row['no_kamar'];
            $stmt2 = $db->prepare("DELETE FROM tb_users WHERE id=? AND role='penghuni'");
            $stmt2->bind_param('i', $id);
            $stmt2->execute();
            // Kembalikan status kamar ke tersedia
            $stmt3 = $db->prepare("UPDATE tb_kamar SET status = 'tersedia' WHERE nama = ?");
            $stmt3->bind_param('s', $no_kamar);
            $stmt3->execute();
        }
    }
    $_SESSION['toast_msg']  = 'Data penghuni berhasil dihapus. Status kamar otomatis menjadi Tersedia.';
    $_SESSION['toast_type'] = 'success';
    redirect('admin.php?page=penghuni');
}