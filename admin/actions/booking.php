<?php
// ============================================================
//  ACTION: Booking (Admin) — Konfirmasi, Tolak, Hapus
// ============================================================

// Handle konfirmasi booking (setujui)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'konfirmasi_booking') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $db = getDB();
        if ($db) {
            $stmt = $db->prepare("SELECT * FROM tb_booking WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();

            if ($booking && $booking['status'] === 'menunggu') {
                $stmt2 = $db->prepare("UPDATE tb_booking SET status = 'disetujui' WHERE id = ?");
                $stmt2->bind_param('i', $id);
                $stmt2->execute();

                $stmt3 = $db->prepare("UPDATE tb_kamar SET status = 'terisi' WHERE nama = ?");
                $stmt3->bind_param('s', $booking['kamar_nama']);
                $stmt3->execute();

                $email = strtolower(str_replace(' ', '.', $booking['nama'])) . '@booking.kost';
                $password = md5('penghuni123');
                $no_kamar = preg_replace('/[^A-Z0-9]/i', '', $booking['kamar_nama']);

                $stmt4 = $db->prepare("SELECT id FROM tb_users WHERE email = ?");
                $stmt4->bind_param('s', $email);
                $stmt4->execute();
                if ($stmt4->get_result()->num_rows === 0) {
                    $stmt5 = $db->prepare(
                        "INSERT INTO tb_users (nama, email, password, role, no_kamar, no_hp, alamat_asal, pekerjaan, created_at)
                         VALUES (?, ?, ?, 'penghuni', ?, ?, '-', 'Mahasiswi', CURDATE())"
                    );
                    $stmt5->bind_param('sssss', $booking['nama'], $email, $password, $no_kamar, $booking['no_hp']);
                    $stmt5->execute();

                    $bulan = $booking['periode'];
                    $stmt6 = $db->prepare(
                        "INSERT INTO tb_transaksi (nama, no_kamar, bulan, jumlah, metode, status, tanggal)
                         VALUES (?, ?, ?, ?, ?, 'lunas', CURDATE())"
                    );
                    $stmt6->bind_param('sssis', $booking['nama'], $no_kamar, $bulan, $booking['kamar_harga'], $booking['metode']);
                    $stmt6->execute();
                }
                $_SESSION['toast_msg']  = 'Booking ' . $booking['kamar_nama'] . ' disetujui. Akun penghuni telah dibuat.';
                $_SESSION['toast_type'] = 'success';
            }
        } else {
            foreach ($_SESSION['demo_booking'] as &$b) {
                if ($b['id'] == $id && $b['status'] === 'menunggu') {
                    $b['status'] = 'disetujui';
                    break;
                }
            }
            unset($b);
            $_SESSION['toast_msg']  = 'Booking disetujui (mode demo).';
            $_SESSION['toast_type'] = 'success';
        }
    }
    redirect('admin.php?page=booking');
}

// Handle tolak booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tolak_booking') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $db = getDB();
        if ($db) {
            $stmt = $db->prepare("UPDATE tb_booking SET status = 'ditolak' WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
        } else {
            foreach ($_SESSION['demo_booking'] as &$b) {
                if ($b['id'] == $id) { $b['status'] = 'ditolak'; break; }
            }
            unset($b);
        }
        $_SESSION['toast_msg']  = 'Booking ditolak.';
        $_SESSION['toast_type'] = 'success';
    }
    redirect('admin.php?page=booking');
}

// Handle hapus booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus_booking') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $db = getDB();
        if ($db) {
            $stmt = $db->prepare("DELETE FROM tb_booking WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
        } else {
            $_SESSION['demo_booking'] = array_values(
                array_filter($_SESSION['demo_booking'] ?? [], fn($b) => $b['id'] != $id)
            );
        }
        $_SESSION['toast_msg']  = 'Booking berhasil dihapus.';
        $_SESSION['toast_type'] = 'success';
    }
    redirect('admin.php?page=booking');
}