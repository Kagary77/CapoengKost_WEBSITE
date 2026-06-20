<?php
// ============================================================
//  ACTION: Kamar (Admin) — CRUD dengan upload foto
//  Status kamar dikelola otomatis oleh sistem
// ============================================================

// Handle tambah/edit kamar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_kamar') {
    $db        = getDB();
    $nama      = sanitize($_POST['nama'] ?? '');
    $tipe      = sanitize($_POST['tipe'] ?? '');
    $harga     = (int)($_POST['harga'] ?? 0);
    $fasilitas = sanitize($_POST['fasilitas'] ?? '');
    $edit_id   = (int)($_POST['edit_id'] ?? 0);
    
    // Upload foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = uploadFotoKamar($_FILES['foto']);
        if (!$foto) {
            $_SESSION['toast_msg'] = 'Gagal upload foto. Pastikan file gambar (max 5MB).';
            $_SESSION['toast_type'] = 'error';
            redirect('admin.php?page=kamar');
            exit;
        }
        // Hapus foto lama jika ada
        if ($edit_id > 0 && $db) {
            $stmt = $db->prepare("SELECT foto FROM tb_kamar WHERE id = ?");
            $stmt->bind_param('i', $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if (!empty($row['foto'])) hapusFotoKamar($row['foto']);
            }
        }
    }

    if ($nama && $tipe && $harga) {
        if ($db) {
            if ($edit_id > 0) {
                if ($foto) {
                    $stmt = $db->prepare("UPDATE tb_kamar SET nama=?, tipe=?, harga=?, fasilitas=?, foto=? WHERE id=?");
                    $stmt->bind_param('ssissi', $nama, $tipe, $harga, $fasilitas, $foto, $edit_id);
                } else {
                    $stmt = $db->prepare("UPDATE tb_kamar SET nama=?, tipe=?, harga=?, fasilitas=? WHERE id=?");
                    $stmt->bind_param('ssisi', $nama, $tipe, $harga, $fasilitas, $edit_id);
                }
                $stmt->execute();
            } else {
                $stmt = $db->prepare("INSERT INTO tb_kamar (nama, tipe, harga, status, fasilitas, foto) VALUES (?, ?, ?, 'tersedia', ?, ?)");
                $stmt->bind_param('ssiss', $nama, $tipe, $harga, $fasilitas, $foto);
                $stmt->execute();
            }
        }
        $_SESSION['toast_msg'] = $edit_id ? 'Data kamar berhasil diperbarui.' : 'Kamar baru berhasil ditambahkan.';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_msg'] = 'Harap isi semua field yang wajib.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('admin.php?page=kamar');
}

// Handle hapus kamar — sekalian hapus foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus_kamar') {
    $db = getDB();
    $id = (int)($_POST['id'] ?? 0);
    if ($id && $db) {
        // Cek apakah kamar sedang terisi
        $stmt = $db->prepare("SELECT id FROM tb_users WHERE no_kamar = (SELECT nama FROM tb_kamar WHERE id = ?)");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['toast_msg'] = 'Kamar sedang terisi oleh penghuni. Hapus penghuni terlebih dahulu.';
            $_SESSION['toast_type'] = 'error';
            redirect('admin.php?page=kamar');
            exit;
        }
        // Ambil nama file foto sebelum hapus
        $stmt2 = $db->prepare("SELECT foto FROM tb_kamar WHERE id = ?");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['foto'])) hapusFotoKamar($row['foto']);
        }
        // Hapus kamar
        $stmt3 = $db->prepare("DELETE FROM tb_kamar WHERE id=?");
        $stmt3->bind_param('i', $id);
        $stmt3->execute();
    }
    $_SESSION['toast_msg'] = 'Kamar berhasil dihapus.';
    $_SESSION['toast_type'] = 'success';
    redirect('admin.php?page=kamar');
}