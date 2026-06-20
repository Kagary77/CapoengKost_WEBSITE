<?php
session_start();

// ============================================================
//  KONFIGURASI DATABASE — Kost Putri Capoeng
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_capoeng');

function getDB() {
    static $conn = null;
    if ($conn !== null) return $conn;

    $c = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($c->connect_error) {
        return null;
    }
    $c->set_charset('utf8mb4');
    $conn = $c;
    return $conn;
}

function dbAvailable() {
    return getDB() !== null;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateTicket() {
    return 'KC-' . strtoupper(substr(md5(uniqid()), 0, 6));
}

// ============================================================
//  UPLOAD BUKTI PEMBAYARAN → uploads/pembayaran/
// ============================================================
function uploadBukti($file) {
    $target_dir = "uploads/pembayaran/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

    $filename = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($file['tmp_name']);
    if ($check === false) return false;

    if (!in_array($imageFileType, ['jpg','jpeg','png','gif','webp'])) return false;
    if ($file['size'] > 5000000) return false;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $filename;
    }
    return false;
}

// ============================================================
//  UPLOAD FOTO KOMPLAIN → uploads/komplain/
// ============================================================
function uploadFotoKomplain($file) {
    $target_dir = "uploads/komplain/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

    $filename = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($file['tmp_name']);
    if ($check === false) return false;

    if (!in_array($imageFileType, ['jpg','jpeg','png','gif','webp'])) return false;
    if ($file['size'] > 5000000) return false;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return 'komplain/' . $filename;
    }
    return false;
}

// ============================================================
//  UPLOAD FOTO KTP → uploads/ktp/
// ============================================================
function uploadFotoKTP($file) {
    $target_dir = "uploads/ktp/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

    $filename = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($file['tmp_name']);
    if ($check === false) return false;

    if (!in_array($imageFileType, ['jpg','jpeg','png','gif','webp'])) return false;
    if ($file['size'] > 5000000) return false;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return 'ktp/' . $filename;
    }
    return false;
}

// ============================================================
//  TRANSAKSI
// ============================================================
function bayarTransaksiWithBukti($id, $metode, $bukti) {
    $db = getDB();
    if (!$db) {
        foreach ($_SESSION['demo_transaksi'] as &$t) {
            if ($t['id'] == $id) {
                $t['status'] = 'pending';
                $t['metode'] = $metode;
                $t['bukti']  = $bukti;
                $t['tanggal'] = date('Y-m-d');
                break;
            }
        }
        return true;
    }
    $stmt = $db->prepare("UPDATE tb_transaksi SET status='pending', metode=?, bukti=?, tanggal=? WHERE id=?");
    $stmt->bind_param('sssi', $metode, $bukti, date('Y-m-d'), $id);
    return $stmt->execute();
}

function validasiTransaksi($id, $status_baru) {
    $db = getDB();
    if (!$db) {
        foreach ($_SESSION['demo_transaksi'] as &$t) {
            if ($t['id'] == $id) {
                $t['status'] = $status_baru;
                if ($status_baru === 'lunas') {
                    $t['tanggal'] = date('Y-m-d');
                }
                break;
            }
        }
        return true;
    }
    $tgl = ($status_baru === 'lunas') ? date('Y-m-d') : null;
    $stmt = $db->prepare("UPDATE tb_transaksi SET status=?, tanggal=? WHERE id=?");
    $stmt->bind_param('ssi', $status_baru, $tgl, $id);
    return $stmt->execute();
}

function getTransaksiAll() {
    $db = getDB();
    if (!$db) return $_SESSION['demo_transaksi'] ?? [];

    $result = $db->query("SELECT * FROM tb_transaksi ORDER BY id ASC");
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    return $rows;
}

function getMyTransaksi() {
    $all        = getTransaksiAll();
    $user_kamar = strtoupper(trim($_SESSION['user_kamar'] ?? ''));
    if (isAdmin()) return $all;
    return array_values(array_filter($all,
        fn($t) => strtoupper(trim($t['no_kamar'])) === $user_kamar
    ));
}

// ============================================================
//  KAMAR
// ============================================================
function getKamarData() {
    $db = getDB();
    if (!$db) return getDemoData('kamar');

    $result = $db->query("SELECT * FROM tb_kamar ORDER BY id ASC");
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    return $rows;
}

function updateKamarStatus($id, $status) {
    $db = getDB();
    if (!$db) return false;

    $stmt = $db->prepare("UPDATE tb_kamar SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    return $stmt->execute();
}

// ============================================================
//  KOMPLAIN
// ============================================================
function getKomplainAll() {
    $db = getDB();
    if (!$db) return $_SESSION['demo_komplain'] ?? [];

    $result = $db->query("SELECT * FROM tb_komplain ORDER BY id DESC");
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    return $rows;
}

function addKomplain($data) {
    $db = getDB();
    if (!$db) {
        $data['id']         = count($_SESSION['demo_komplain']) + 1;
        $data['created_at'] = date('Y-m-d');
        $data['foto']       = $data['foto'] ?? '';
        $_SESSION['demo_komplain'][] = $data;
        return true;
    }

    $stmt = $db->prepare(
        "INSERT INTO tb_komplain (no_tiket, nama, no_kamar, no_hp, kategori, deskripsi, status, foto, created_at)
         VALUES (?, ?, ?, ?, ?, ?, 'baru', ?, CURDATE())"
    );
    $stmt->bind_param('sssssss',
        $data['no_tiket'], $data['nama'], $data['no_kamar'],
        $data['no_hp'],    $data['kategori'], $data['deskripsi'],
        $data['foto']
    );
    return $stmt->execute();
}

function updateKomplainStatus($id, $status) {
    $db = getDB();
    if (!$db) {
        foreach ($_SESSION['demo_komplain'] as &$k) {
            if ($k['id'] == $id) { $k['status'] = $status; break; }
        }
        return true;
    }

    $stmt = $db->prepare("UPDATE tb_komplain SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    return $stmt->execute();
}

function hapusKomplain($id) {
    $db = getDB();
    if (!$db) {
        $_SESSION['demo_komplain'] = array_values(
            array_filter($_SESSION['demo_komplain'] ?? [], fn($k) => $k['id'] != $id)
        );
        return true;
    }
    $stmt = $db->prepare("DELETE FROM tb_komplain WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}

// ============================================================
//  FUNGSI PESAN
// ============================================================
function getPesanAll() {
    $db = getDB();
    if (!$db) return $_SESSION['demo_pesan'] ?? [];
    $result = $db->query("SELECT * FROM tb_pesan ORDER BY id DESC");
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    return $rows;
}

function hapusPesan($id) {
    $db = getDB();
    if (!$db) {
        $_SESSION['demo_pesan'] = array_values(
            array_filter($_SESSION['demo_pesan'] ?? [], fn($p) => $p['id'] != $id)
        );
        return true;
    }
    $stmt = $db->prepare("DELETE FROM tb_pesan WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}

function updateStatusPesan($id, $status) {
    $db = getDB();
    if (!$db) {
        foreach ($_SESSION['demo_pesan'] as &$p) {
            if ($p['id'] == $id) { $p['status'] = $status; break; }
        }
        unset($p);
        return true;
    }
    $stmt = $db->prepare("UPDATE tb_pesan SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    return $stmt->execute();
}

// ============================================================
//  FUNGSI BOOKING
// ============================================================
function getBookingAll() {
    $db = getDB();
    if (!$db) return $_SESSION['demo_booking'] ?? [];
    $result = $db->query("SELECT * FROM tb_booking ORDER BY id DESC");
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    return $rows;
}

function konfirmasiBooking($id) {
    $db = getDB();
    if (!$db) {
        foreach ($_SESSION['demo_booking'] as &$b) {
            if ($b['id'] == $id) { $b['status'] = 'disetujui'; break; }
        }
        unset($b);
        return true;
    }
    $stmt = $db->prepare("UPDATE tb_booking SET status = 'disetujui' WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}

function tolakBooking($id) {
    $db = getDB();
    if (!$db) {
        foreach ($_SESSION['demo_booking'] as &$b) {
            if ($b['id'] == $id) { $b['status'] = 'ditolak'; break; }
        }
        unset($b);
        return true;
    }
    $stmt = $db->prepare("UPDATE tb_booking SET status = 'ditolak' WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}

function hapusBooking($id) {
    $db = getDB();
    if (!$db) {
        $_SESSION['demo_booking'] = array_values(
            array_filter($_SESSION['demo_booking'] ?? [], fn($b) => $b['id'] != $id)
        );
        return true;
    }
    $stmt = $db->prepare("DELETE FROM tb_booking WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}

// ============================================================
//  USER / AUTH (dengan foto KTP)
// ============================================================
function loginUser($email, $password) {
    $db = getDB();
    $hashed = md5($password);

    if (!$db) {
        foreach (getDemoData('users') as $u) {
            if ($u['email'] === $email && $u['password'] === $hashed) {
                return $u;
            }
        }
        return null;
    }

    $stmt = $db->prepare("SELECT * FROM tb_users WHERE email = ? AND password = ? LIMIT 1");
    $stmt->bind_param('ss', $email, $hashed);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}

function updateUserProfile($id, $nama, $no_hp, $alamat, $pekerjaan, $foto_ktp = null) {
    $db = getDB();
    if (!$db) {
        $_SESSION['user_nama']      = $nama;
        $_SESSION['user_hp']        = $no_hp;
        $_SESSION['user_alamat']    = $alamat;
        $_SESSION['user_pekerjaan'] = $pekerjaan;
        if ($foto_ktp) $_SESSION['user_foto_ktp'] = $foto_ktp;
        return true;
    }

    if ($foto_ktp) {
        $stmt = $db->prepare(
            "UPDATE tb_users SET nama=?, no_hp=?, alamat_asal=?, pekerjaan=?, foto_ktp=? WHERE id=?"
        );
        $stmt->bind_param('sssssi', $nama, $no_hp, $alamat, $pekerjaan, $foto_ktp, $id);
    } else {
        $stmt = $db->prepare(
            "UPDATE tb_users SET nama=?, no_hp=?, alamat_asal=?, pekerjaan=? WHERE id=?"
        );
        $stmt->bind_param('ssssi', $nama, $no_hp, $alamat, $pekerjaan, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['user_nama']      = $nama;
        $_SESSION['user_hp']        = $no_hp;
        $_SESSION['user_alamat']    = $alamat;
        $_SESSION['user_pekerjaan'] = $pekerjaan;
        if ($foto_ktp) $_SESSION['user_foto_ktp'] = $foto_ktp;
        return true;
    }
    return false;
}

function updateUserPassword($id, $new_password) {
    $db = getDB();
    $hashed = md5($new_password);
    if (!$db) {
        $_SESSION['user_pass'] = $hashed;
        return true;
    }

    $stmt = $db->prepare("UPDATE tb_users SET password=? WHERE id=?");
    $stmt->bind_param('si', $hashed, $id);
    if ($stmt->execute()) {
        $_SESSION['user_pass'] = $hashed;
        return true;
    }
    return false;
}

// ============================================================
//  DATA DEMO
// ============================================================
function getDemoData($key) {
    $data = [
        'kamar' => [],
        'users' => [],
    ];
    return $data[$key] ?? [];
}

if (!dbAvailable()) {
    if (!isset($_SESSION['demo_komplain']))  $_SESSION['demo_komplain']  = [];
    if (!isset($_SESSION['demo_transaksi'])) $_SESSION['demo_transaksi'] = [];
    if (!isset($_SESSION['demo_pesan']))     $_SESSION['demo_pesan']     = [];
    if (!isset($_SESSION['demo_booking']))   $_SESSION['demo_booking']   = [];
}