<?php
// ============================================================
//  ACTION: Booking (Tamu)
//  Menyimpan booking dari tamu ke database
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'booking_kamar_tamu') {
    $nama        = sanitize($_POST['nama'] ?? '');
    $no_hp       = sanitize($_POST['no_hp'] ?? '');
    $kamar_nama  = sanitize($_POST['kamar_nama'] ?? '');
    $kamar_harga = (int)($_POST['kamar_harga'] ?? 0);
    $metode      = sanitize($_POST['metode'] ?? '');
    $periode     = sanitize($_POST['periode'] ?? '');

    if ($nama && $no_hp && $kamar_nama && $metode && $periode) {
        $db = getDB();

        // Cek apakah kamar masih tersedia
        if ($db) {
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

        // Upload bukti pembayaran
        $bukti = '';
        if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
            $bukti = uploadBukti($_FILES['bukti']);
            if (!$bukti) {
                $_SESSION['toast_msg']  = 'Gagal upload bukti. Pastikan file gambar (max 5MB).';
                $_SESSION['toast_type'] = 'error';
                redirect('index.php?page=kamar');
                exit;
            }
        } else {
            $_SESSION['toast_msg']  = 'Harap upload bukti pembayaran.';
            $_SESSION['toast_type'] = 'error';
            redirect('index.php?page=kamar');
            exit;
        }

        // Format periode
        $periode_formatted = date('F Y', strtotime($periode . '-01'));
        $bulan_id = [
            'January'=>'Januari','February'=>'Februari','March'=>'Maret',
            'April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli',
            'August'=>'Agustus','September'=>'September','October'=>'Oktober',
            'November'=>'November','December'=>'Desember'
        ];
        foreach ($bulan_id as $en => $id) $periode_formatted = str_replace($en, $id, $periode_formatted);

        // Simpan ke tb_booking
        if ($db) {
            $stmt = $db->prepare(
                "INSERT INTO tb_booking (nama, no_hp, kamar_nama, kamar_harga, periode, metode, bukti, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu', NOW())"
            );
            $stmt->bind_param('sssssss', $nama, $no_hp, $kamar_nama, $kamar_harga, $periode_formatted, $metode, $bukti);
            $stmt->execute();
        } else {
            // Mode demo
            if (!isset($_SESSION['demo_booking'])) $_SESSION['demo_booking'] = [];
            $_SESSION['demo_booking'][] = [
                'id'          => count($_SESSION['demo_booking']) + 1,
                'nama'        => $nama,
                'no_hp'       => $no_hp,
                'kamar_nama'  => $kamar_nama,
                'kamar_harga' => $kamar_harga,
                'periode'     => $periode_formatted,
                'metode'      => $metode,
                'bukti'       => $bukti,
                'status'      => 'menunggu',
                'created_at'  => date('Y-m-d H:i:s')
            ];
        }

        $_SESSION['toast_msg']  = "Booking $kamar_nama berhasil dikirim! Admin akan menghubungi Anda via WhatsApp.";
        $_SESSION['toast_type'] = 'success';
        redirect('index.php?page=kamar&booked=1');
    } else {
        $_SESSION['toast_msg']  = 'Harap lengkapi semua data booking.';
        $_SESSION['toast_type'] = 'error';
        redirect('index.php?page=kamar');
    }
}