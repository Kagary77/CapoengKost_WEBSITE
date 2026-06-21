<?php
// ============================================================
//  ACTION: Transaksi (Admin) — ALUR BARU
// ============================================================

// Tambah tagihan baru → status 'terkirim'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah_tagihan') {
    $nama      = sanitize($_POST['nama'] ?? '');
    $no_kamar  = sanitize($_POST['no_kamar'] ?? '');
    $bulan_raw = $_POST['bulan'] ?? '';
    $jumlah    = (int)($_POST['jumlah'] ?? 0);
    $metode    = sanitize($_POST['metode'] ?? '-');

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

        if (buatTagihan($nama, $no_kamar, $bulan, $jumlah, $metode)) {
            $_SESSION['toast_msg']  = 'Tagihan baru berhasil dikirim ke penghuni (status: terkirim).';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_msg']  = 'Gagal membuat tagihan.';
            $_SESSION['toast_type'] = 'error';
        }
    } else {
        $_SESSION['toast_msg']  = 'Harap isi semua field yang wajib.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('admin.php?page=transaksi');
}

// Validasi transaksi (lunas / gagal) oleh admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'validasi_transaksi') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    if ($id && in_array($status, ['lunas', 'gagal'])) {
        if (validasiTransaksi($id, $status)) {
            $msg = $status === 'lunas' 
                ? '✅ Pembayaran disetujui! Status menjadi Lunas.' 
                : '❌ Pembayaran ditolak. Tagihan otomatis dikirim ulang ke penghuni (status: terkirim).';
            $_SESSION['toast_msg']  = $msg;
            $_SESSION['toast_type'] = $status === 'lunas' ? 'success' : 'error';
        } else {
            $_SESSION['toast_msg']  = 'Gagal memvalidasi transaksi.';
            $_SESSION['toast_type'] = 'error';
        }
    } else {
        $_SESSION['toast_msg']  = 'Status tidak valid.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('admin.php?page=transaksi');
}

// Hapus transaksi
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