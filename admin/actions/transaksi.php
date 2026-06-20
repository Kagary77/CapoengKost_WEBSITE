<?php
// ============================================================
//  ACTION: Transaksi (Admin) — CRUD & Validasi
// ============================================================

// Handle update status transaksi (admin) — TIDAK DIPAKAI LAGI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_transaksi') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    $metode = sanitize($_POST['metode'] ?? '-');
    $db     = getDB();

    if ($id && in_array($status, ['lunas', 'belum_bayar', 'pending'])) {
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

// Handle tambah tagihan baru — status awal otomatis PENDING
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah_tagihan') {
    $db        = getDB();
    $nama      = sanitize($_POST['nama'] ?? '');
    $no_kamar  = sanitize($_POST['no_kamar'] ?? '');
    $bulan_raw = $_POST['bulan'] ?? '';
    $jumlah    = (int)($_POST['jumlah'] ?? 0);
    $metode    = sanitize($_POST['metode'] ?? '-');
    $status    = 'pending';

    if ($nama && $no_kamar && $bulan_raw && $jumlah) {
        $bulan_timestamp = strtotime($bulan_raw . '-01');
        $bulan_en = date('F Y', $bulan_timestamp);
        $bulan_id = [
            'January'=>'Januari','February'=>'Februari','March'=>'Maret',
            'April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli',
            'August'=>'Agustus','September'=>'September','October'=>'Oktober',
            'November'=>'November','December'=>'Desember'
        ];
        $bulan = str_replace(array_keys($bulan_id), array_values($bulan_id), $bulan_en);

        if ($db) {
            $stmt = $db->prepare(
                "INSERT INTO tb_transaksi (nama, no_kamar, bulan, jumlah, metode, status)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('sssiss', $nama, $no_kamar, $bulan, $jumlah, $metode, $status);
            $stmt->execute();
        } else {
            $new = [
                'id' => count($_SESSION['demo_transaksi']) + 1,
                'nama' => $nama,
                'no_kamar' => $no_kamar,
                'bulan' => $bulan,
                'jumlah' => $jumlah,
                'metode' => $metode,
                'status' => 'pending',
                'tanggal' => null,
                'bukti' => null,
            ];
            $_SESSION['demo_transaksi'][] = $new;
        }
        $_SESSION['toast_msg']  = 'Tagihan baru berhasil ditambahkan dengan status Pending.';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_msg']  = 'Harap isi semua field yang wajib.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('admin.php?page=transaksi');
}

// Handle hapus transaksi
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

// Handle validasi transaksi (lunas / gagal) oleh admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'validasi_transaksi') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    if ($id && in_array($status, ['lunas', 'belum_bayar'])) {
        validasiTransaksi($id, $status);
        $_SESSION['toast_msg']  = $status === 'lunas' 
            ? '✅ Pembayaran disetujui! Status menjadi Lunas.' 
            : '❌ Pembayaran ditolak. Status kembali ke Belum Bayar.';
        $_SESSION['toast_type'] = $status === 'lunas' ? 'success' : 'error';
    } else {
        $_SESSION['toast_msg']  = 'Status tidak valid.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('admin.php?page=transaksi');
}