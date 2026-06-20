<?php
// ============================================================
//  ACTION: Kamar (Admin) — CRUD tanpa update status manual
//  Status kamar dikelola otomatis oleh sistem
// ============================================================

// Handle tambah/edit kamar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_kamar') {
    $db        = getDB();
    $nama      = sanitize($_POST['nama'] ?? '');
    $tipe      = sanitize($_POST['tipe'] ?? '');
    $harga     = (int)($_POST['harga'] ?? 0);
    $status    = sanitize($_POST['status'] ?? 'tersedia');
    $fasilitas = sanitize($_POST['fasilitas'] ?? '');
    $edit_id   = (int)($_POST['edit_id'] ?? 0);

    if ($nama && $tipe && $harga) {
        if ($db) {
            if ($edit_id > 0) {
                $stmt = $db->prepare("UPDATE tb_kamar SET nama=?, tipe=?, harga=?, fasilitas=? WHERE id=?");
                $stmt->bind_param('ssisi', $nama, $tipe, $harga, $fasilitas, $edit_id);
                $stmt->execute();
            } else {
                $stmt = $db->prepare("INSERT INTO tb_kamar (nama, tipe, harga, status, fasilitas, foto) VALUES (?, ?, ?, 'tersedia', ?, '')");
                $stmt->bind_param('ssiss', $nama, $tipe, $harga, $fasilitas);
                $stmt->execute();
            }
        }
        $_SESSION['toast_msg']  = $edit_id ? 'Data kamar berhasil diperbarui.' : 'Kamar baru berhasil ditambahkan.';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_msg']  = 'Harap isi semua field yang wajib.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('admin.php?page=kamar');
}

// Handle hapus kamar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus_kamar') {
    $db = getDB();
    $id = (int)($_POST['id'] ?? 0);
    if ($id && $db) {
        $stmt = $db->prepare("SELECT id FROM tb_users WHERE no_kamar = (SELECT nama FROM tb_kamar WHERE id = ?)");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['toast_msg']  = 'Kamar sedang terisi oleh penghuni. Hapus penghuni terlebih dahulu.';
            $_SESSION['toast_type'] = 'error';
            redirect('admin.php?page=kamar');
            exit;
        }
        $stmt2 = $db->prepare("DELETE FROM tb_kamar WHERE id=?");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
    }
    $_SESSION['toast_msg']  = 'Kamar berhasil dihapus.';
    $_SESSION['toast_type'] = 'success';
    redirect('admin.php?page=kamar');
}