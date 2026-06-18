<?php
// ============================================================
//  ACTION: Kontak (Publik)
//  Menyimpan pesan dari tamu ke database
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'kirim_pesan') {
    $nama  = sanitize($_POST['nama'] ?? '');
    $nohp  = sanitize($_POST['no_hp'] ?? '');
    $topik = sanitize($_POST['topik'] ?? '');
    $pesan = sanitize($_POST['pesan'] ?? '');

    if ($nama && $nohp && $topik && $pesan) {
        $db = getDB();
        if ($db) {
            $stmt = $db->prepare(
                "INSERT INTO tb_pesan (nama, no_hp, topik, pesan, status, created_at)
                 VALUES (?, ?, ?, ?, 'baru', NOW())"
            );
            $stmt->bind_param('ssss', $nama, $nohp, $topik, $pesan);
            $stmt->execute();
        } else {
            // Mode demo — simpan ke session
            if (!isset($_SESSION['demo_pesan'])) $_SESSION['demo_pesan'] = [];
            $_SESSION['demo_pesan'][] = [
                'id'         => count($_SESSION['demo_pesan']) + 1,
                'nama'       => $nama,
                'no_hp'      => $nohp,
                'topik'      => $topik,
                'pesan'      => $pesan,
                'status'     => 'baru',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        $_SESSION['toast_msg']  = 'Pesan berhasil dikirim! Pengelola akan menghubungi kamu segera.';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_msg']  = 'Harap isi semua field yang wajib.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('index.php?page=kontak&sent=1');
}