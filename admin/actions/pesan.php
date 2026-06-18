<?php
// ============================================================
//  ACTION: Pesan (Admin) — Hapus & Update Status
// ============================================================

// Handle update status pesan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status_pesan') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');

    if ($id && in_array($status, ['baru', 'dibaca', 'dibalas'])) {
        $db = getDB();
        if ($db) {
            $stmt = $db->prepare("UPDATE tb_pesan SET status = ? WHERE id = ?");
            $stmt->bind_param('si', $status, $id);
            $stmt->execute();
        } else {
            // Mode demo
            foreach ($_SESSION['demo_pesan'] as &$p) {
                if ($p['id'] == $id) { $p['status'] = $status; break; }
            }
            unset($p);
        }
        $_SESSION['toast_msg']  = 'Status pesan berhasil diperbarui.';
        $_SESSION['toast_type'] = 'success';
    }
    redirect('admin.php?page=pesan');
}

// Handle hapus pesan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus_pesan') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $db = getDB();
        if ($db) {
            $stmt = $db->prepare("DELETE FROM tb_pesan WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
        } else {
            // Mode demo
            $_SESSION['demo_pesan'] = array_values(
                array_filter($_SESSION['demo_pesan'] ?? [], fn($p) => $p['id'] != $id)
            );
        }
        $_SESSION['toast_msg']  = 'Pesan berhasil dihapus.';
        $_SESSION['toast_type'] = 'success';
    }
    redirect('admin.php?page=pesan');
}