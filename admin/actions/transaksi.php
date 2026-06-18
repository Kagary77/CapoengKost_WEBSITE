<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_transaksi') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    $metode = sanitize($_POST['metode'] ?? '-');
    $db     = getDB();

    if ($id && in_array($status, ['lunas', 'belum_bayar', 'menunggu_konfirmasi', 'pending'])) {
        if ($db) {
            $tgl = $status === 'lunas' ? date('Y-m-d') : null;
            $stmt = $db->prepare("UPDATE tb_transaksi SET status=?, metode=?, tanggal=? WHERE id=?");
            $stmt->bind_param('sssi', $status, $metode, $tgl, $id);
            $stmt->execute();
        } else {
            foreach ($_SESSION['demo_transaksi'] as &$t) {
                if ($t['id'] == $id) {
                    $t['status']  = $status;
                    $t['metode']  = $metode;
                    $t['tanggal'] = $status === 'lunas' ? date('Y-m-d') : '-';
                    break;
                }
            }
            unset($t);
        }
        $_SESSION['toast_msg']  = 'Status transaksi berhasil diperbarui.';
        $_SESSION['toast_type'] = 'success';
    }
    redirect('admin.php?page=transaksi');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah_tagihan') {
    $db        = getDB();
    $nama      = sanitize($_POST['nama'] ?? '');
    $no_kamar  = sanitize($_POST['no_kamar'] ?? '');
    $bulan     = sanitize($_POST['bulan'] ?? '');
    $jumlah    = (int)($_POST['jumlah'] ?? 0);
    $metode    = sanitize($_POST['metode'] ?? '-');
    $status    = sanitize($_POST['status'] ?? 'belum_bayar');

    if ($nama && $no_kamar && $bulan && $jumlah) {
        if ($db) {
            $tgl = $status === 'lunas' ? date('Y-m-d') : null;
            $stmt = $db->prepare(
                "INSERT INTO tb_transaksi (nama, no_kamar, bulan, jumlah, metode, status, tanggal)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('sssisss', $nama, $no_kamar, $bulan, $jumlah, $metode, $status, $tgl);
            $stmt->execute();
        } else {
            $new = [
                'id' => count($_SESSION['demo_transaksi']) + 1,
                'nama' => $nama,
                'no_kamar' => $no_kamar,
                'bulan' => $bulan,
                'jumlah' => $jumlah,
                'metode' => $metode,
                'status' => $status,
                'tanggal' => $status === 'lunas' ? date('Y-m-d') : '-',
                'bukti' => null,
            ];
            $_SESSION['demo_transaksi'][] = $new;
        }
        $_SESSION['toast_msg']  = 'Tagihan baru berhasil ditambahkan.';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_msg']  = 'Harap isi semua field yang wajib.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('admin.php?page=transaksi');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus_transaksi') {
    $db = getDB();
    $id = (int)($_POST['id'] ?? 0);
    if ($id && $db) {
        $stmt = $db->prepare("DELETE FROM tb_transaksi WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    } elseif ($id) {
        $_SESSION['demo_transaksi'] = array_values(
            array_filter($_SESSION['demo_transaksi'] ?? [], fn($t) => $t['id'] != $id)
        );
    }
    $_SESSION['toast_msg']  = 'Transaksi berhasil dihapus.';
    $_SESSION['toast_type'] = 'success';
    redirect('admin.php?page=transaksi');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'validasi_transaksi') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    if ($id && in_array($status, ['lunas', 'belum_bayar'])) {
        validasiTransaksi($id, $status);
        $_SESSION['toast_msg']  = 'Status transaksi berhasil diubah.';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_msg']  = 'Status tidak valid.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('admin.php?page=transaksi');
}