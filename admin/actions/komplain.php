<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    if ($id && in_array($status, ['baru', 'diproses', 'selesai'])) {
        updateKomplainStatus($id, $status);
        $_SESSION['toast_msg']  = 'Status komplain berhasil diperbarui.';
        $_SESSION['toast_type'] = 'success';
    }
    redirect('admin.php?page=komplain');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus_komplain') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        hapusKomplain($id);
        $_SESSION['toast_msg']  = 'Komplain berhasil dihapus.';
        $_SESSION['toast_type'] = 'success';
    }
    redirect('admin.php?page=komplain');
}