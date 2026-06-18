<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status_kamar') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    if ($id && in_array($status, ['tersedia', 'terisi'])) {
        updateKamarStatus($id, $status);
        $_SESSION['toast_msg']  = 'Status kamar berhasil diperbarui.';
        $_SESSION['toast_type'] = 'success';
    }
    redirect('admin.php?page=kamar');
}

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
                $stmt = $db->prepare("UPDATE tb_kamar SET nama=?, tipe=?, harga=?, status=?, fasilitas=? WHERE id=?");
                $stmt->bind_param('ssissi', $nama, $tipe, $harga, $status, $fasilitas, $edit_id);
            } else {
                $stmt = $db->prepare("INSERT INTO tb_kamar (nama, tipe, harga, status, fasilitas, foto) VALUES (?, ?, ?, ?, ?, '')");
                $stmt->bind_param('ssiss', $nama, $tipe, $harga, $status, $fasilitas);
            }
            $stmt->execute();
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
        $stmt = $db->prepare("DELETE FROM tb_kamar WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    $_SESSION['toast_msg']  = 'Kamar berhasil dihapus.';
    $_SESSION['toast_type'] = 'success';
    redirect('admin.php?page=kamar');
}
