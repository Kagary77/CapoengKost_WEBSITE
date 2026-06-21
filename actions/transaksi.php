<?php
// ============================================================
//  ACTION: Transaksi — Booking & Pembayaran (Publik / Penghuni)
// ============================================================

// Handle POST: Booking kamar baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'booking_kamar') {
    $nama        = sanitize($_POST['nama'] ?? '');
    $no_hp       = sanitize($_POST['no_hp'] ?? '');
    $kamar_nama  = sanitize($_POST['kamar_nama'] ?? '');
    $kamar_harga = (int)($_POST['kamar_harga'] ?? 0);
    $metode      = sanitize($_POST['metode'] ?? '');
    $periode_raw = $_POST['periode'] ?? '';

    // ── VALIDASI: User login sudah punya kamar? ──
    $isLoggedIn = isLoggedIn();
    $user_id    = $_SESSION['user_id'] ?? 0;
    $user_kamar = $_SESSION['user_kamar'] ?? null;

    if ($isLoggedIn && !empty($user_kamar) && $user_kamar !== '-') {
        $_SESSION['toast_msg']  = '⚠️ Kamu sudah memiliki kamar ' . $user_kamar . '. Tidak bisa booking kamar lain.';
        $_SESSION['toast_type'] = 'error';
        redirect('index.php?page=kamar');
        exit;
    }

    if ($nama && $no_hp && $kamar_nama && $metode && $periode_raw) {
        // Cek apakah kamar masih tersedia
        $db = getDB();
        if ($db && $kamar_nama) {
            $stmt = $db->prepare("SELECT status FROM tb_kamar WHERE nama = ?");
            $stmt->bind_param('s', $kamar_nama);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if ($row['status'] === 'terisi') {
                    $_SESSION['toast_msg']  = 'Maaf, kamar ini sudah terisi.';
                    $_SESSION['toast_type'] = 'error';
                    redirect('index.php?page=kamar');
                    exit;
                }
            }
        }

        $bulan = date('F Y', strtotime($periode_raw . '-01'));
        $bulan_id = [
            'January'=>'Januari','February'=>'Februari','March'=>'Maret',
            'April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli',
            'August'=>'Agustus','September'=>'September','October'=>'Oktober',
            'November'=>'November','December'=>'Desember'
        ];
        foreach ($bulan_id as $en => $id) $bulan = str_replace($en, $id, $bulan);

        preg_match('/([A-Z]\d+)/i', $kamar_nama, $match);
        $no_kamar = strtoupper($match[1] ?? substr($kamar_nama, -2));

        // ── Jika user login, update no_kamar di tb_users ──
        if ($isLoggedIn && $user_id) {
            if ($db) {
                // Update no_kamar user
                $stmt = $db->prepare("UPDATE tb_users SET no_kamar = ? WHERE id = ?");
                $stmt->bind_param('si', $no_kamar, $user_id);
                $stmt->execute();

                // Update status kamar menjadi terisi
                $stmt2 = $db->prepare("UPDATE tb_kamar SET status = 'terisi' WHERE nama = ?");
                $stmt2->bind_param('s', $kamar_nama);
                $stmt2->execute();

                // Update session
                $_SESSION['user_kamar'] = $no_kamar;
            } else {
                // Mode demo
                $_SESSION['user_kamar'] = $no_kamar;
                if (!isset($_SESSION['demo_kamar_override'])) $_SESSION['demo_kamar_override'] = [];
                $_SESSION['demo_kamar_override'][$no_kamar] = 'terisi';
            }
        }

        // Simpan transaksi — status diubah menjadi 'pending' agar terlihat di admin
        $new_trans = [
            'id'       => count($_SESSION['demo_transaksi'] ?? []) + 1,
            'nama'     => $nama,
            'no_kamar' => $no_kamar,
            'bulan'    => $bulan,
            'jumlah'   => $kamar_harga,
            'metode'   => $metode,
            'status'   => 'pending',
            'tanggal'  => date('Y-m-d'),
            'no_hp'    => $no_hp,
            'bukti'    => null,
        ];
        $_SESSION['demo_transaksi'][] = $new_trans;

        $_SESSION['toast_msg']  = "Booking $kamar_nama berhasil! Menunggu konfirmasi pengelola.";
        $_SESSION['toast_type'] = 'success';
        redirect('index.php?page=transaksi&booked=1');
    } else {
        $_SESSION['toast_msg']  = 'Harap lengkapi semua data booking.';
        $_SESSION['toast_type'] = 'error';
        redirect('index.php?page=kamar');
    }
}

// Handle POST: Konfirmasi bayar dengan upload bukti
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bayar') {
    $id     = (int)($_POST['id'] ?? 0);
    $metode = sanitize($_POST['metode'] ?? '');

    if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['toast_msg']  = 'Harap upload bukti pembayaran.';
        $_SESSION['toast_type'] = 'error';
        redirect('index.php?page=transaksi');
    }
    $bukti = uploadBukti($_FILES['bukti']);
    if (!$bukti) {
        $_SESSION['toast_msg']  = 'Gagal upload bukti. Pastikan file gambar (max 5MB).';
        $_SESSION['toast_type'] = 'error';
        redirect('index.php?page=transaksi');
    }

    $all_trans = getTransaksiAll();
    $target = null;
    foreach ($all_trans as $t) {
        if ($t['id'] == $id) { $target = $t; break; }
    }
    if (!$target) {
        $_SESSION['toast_msg']  = 'Transaksi tidak ditemukan.';
        $_SESSION['toast_type'] = 'error';
        redirect('index.php?page=transaksi');
    }

    $boleh = isAdmin() || (strtoupper(trim($target['no_kamar'])) === strtoupper(trim($_SESSION['user_kamar'] ?? '')));
    if (!$boleh) {
        $_SESSION['toast_msg']  = 'Akses ditolak.';
        $_SESSION['toast_type'] = 'error';
        redirect('index.php?page=transaksi');
    }

    if (bayarTransaksiWithBukti($id, $metode, $bukti)) {
        $_SESSION['toast_msg']  = 'Bukti pembayaran terkirim! Menunggu verifikasi admin.';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_msg']  = 'Gagal mengirim bukti.';
        $_SESSION['toast_type'] = 'error';
    }
    redirect('index.php?page=transaksi');
}