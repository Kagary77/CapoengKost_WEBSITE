<?php
// ============================================================
//  ACTION: Komplain (Publik / Penghuni)
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_komplain') {
    $nama      = sanitize($_POST['nama'] ?? '');
    $nokamar   = sanitize($_POST['no_kamar'] ?? '');
    $nohp      = sanitize($_POST['no_hp'] ?? '');
    $kategori  = sanitize($_POST['kategori'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');

    if ($nama && $nokamar && $kategori && $deskripsi) {
        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto = uploadFotoKomplain($_FILES['foto']);
        }

        $tiket = generateTicket();
        addKomplain([
            'no_tiket'  => $tiket,
            'nama'      => $nama,
            'no_kamar'  => $nokamar,
            'no_hp'     => $nohp,
            'kategori'  => $kategori,
            'deskripsi' => $deskripsi,
            'status'    => 'baru',
            'foto'      => $foto,
        ]);
        $_SESSION['last_tiket'] = $tiket;
        redirect('index.php?page=komplain&success=1');
    }
    $error = 'Harap isi semua field yang wajib diisi.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status' && isAdmin()) {
    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    if ($id && in_array($status, ['baru', 'diproses', 'selesai'])) {
        updateKomplainStatus($id, $status);
        $_SESSION['toast_msg']  = 'Status komplain berhasil diperbarui.';
        $_SESSION['toast_type'] = 'success';
    }
    redirect('index.php?page=komplain');
}