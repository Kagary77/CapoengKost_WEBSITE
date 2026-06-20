<?php
// ============================================================
//  ACTION: Penghuni (Admin) — CRUD dengan validasi kamar unik
//  Status kamar otomatis dikelola sistem
// ============================================================

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
            // Cek apakah kamar sudah ditempati
            $stmt = $db->prepare("SELECT id FROM tb_users WHERE no_kamar = ?");
            $stmt->bind_param('s', $no_kamar);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $_SESSION['toast_msg']  = 'Kamar ' . $no_kamar . ' sudah ditempati penghuni lain.';
                $_SESSION['toast_type'] = 'error';
                redirect('admin.php?page=penghuni');
                exit;
            }
            // Cek apakah email sudah terdaftar
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

        $hashed = md5($password);
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

// Handle reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset_password') {
    $db  = getDB();
    $id  = (int)($_POST['id'] ?? 0);
    $new = 'penghuni123';
    if ($id && $db) {
        $stmt = $db->prepare("UPDATE tb_users SET password=? WHERE id=?");
        $hashed = md5($new);
        $stmt->bind_param('si', $hashed, $id);
        $stmt->execute();
    }
    $_SESSION['toast_msg']  = 'Password direset ke "penghuni123".';
    $_SESSION['toast_type'] = 'success';
    redirect('admin.php?page=penghuni');
}

// Handle hapus penghuni — sekalian update status kamar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus_penghuni') {
    $db = getDB();
    $id = (int)($_POST['id'] ?? 0);
    if ($id && $db) {
        // Dapatkan no_kamar user sebelum dihapus
        $stmt = $db->prepare("SELECT no_kamar FROM tb_users WHERE id=? AND role='penghuni'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $no_kamar = $row['no_kamar'];
            // Hapus user
            $stmt2 = $db->prepare("DELETE FROM tb_users WHERE id=? AND role='penghuni'");
            $stmt2->bind_param('i', $id);
            $stmt2->execute();
            // Update status kamar menjadi tersedia
            $stmt3 = $db->prepare("UPDATE tb_kamar SET status = 'tersedia' WHERE nama = ?");
            $stmt3->bind_param('s', $no_kamar);
            $stmt3->execute();
        }
    }
    $_SESSION['toast_msg']  = 'Data penghuni berhasil dihapus. Status kamar otomatis menjadi Tersedia.';
    $_SESSION['toast_type'] = 'success';
    redirect('admin.php?page=penghuni');
}