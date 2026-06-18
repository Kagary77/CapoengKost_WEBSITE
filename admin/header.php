<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Dashboard' ?> — Admin Panel</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css"> 
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

<?php
$kamar_all     = getKamarData();
$komplain_all  = getKomplainAll();
$transaksi_all = getTransaksiAll();
$komplain_baru = count(array_filter($komplain_all, fn($k) => $k['status'] === 'baru'));
$belum_bayar   = count(array_filter($transaksi_all, fn($t) => $t['status'] === 'belum_bayar'));

// Hitung pesan baru
$db_pesan = getDB();
if ($db_pesan) {
    $result = $db_pesan->query("SELECT COUNT(*) as total FROM tb_pesan WHERE status='baru'");
    $row = $result->fetch_assoc();
    $pesan_baru = $row['total'] ?? 0;
} else {
    $pesan_baru = count(array_filter($_SESSION['demo_pesan'] ?? [], fn($p) => $p['status'] === 'baru'));
}
?>

<div class="admin-layout">
  <div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>

  <aside class="admin-sidebar" id="sidebar">
    <div class="admin-sidebar-header">
      <div class="admin-brand">
        <div class="admin-logo">👑</div>
        <div>
          <div class="admin-brand-name">Admin Panel</div>
          <div class="admin-brand-sub">Kost Putri Capoeng</div>
        </div>
      </div>
    </div>

    <nav class="admin-nav">
      <div class="admin-nav-section">
        <div class="admin-nav-label">Utama</div>
        <a href="admin.php?page=dashboard" class="admin-nav-item <?= $activePage==='dashboard'?'active':'' ?>">
          <span class="ani">📊</span> Dashboard
        </a>
      </div>

      <div class="admin-nav-section">
        <div class="admin-nav-label">Kelola Data</div>
        <a href="admin.php?page=kamar" class="admin-nav-item <?= $activePage==='kamar'?'active':'' ?>">
          <span class="ani">🏠</span> Kamar
        </a>
        <a href="admin.php?page=penghuni" class="admin-nav-item <?= $activePage==='penghuni'?'active':'' ?>">
          <span class="ani">👩</span> Penghuni
        </a>
        <a href="admin.php?page=komplain" class="admin-nav-item <?= $activePage==='komplain'?'active':'' ?>">
          <span class="ani">📋</span> Komplain
          <?php if($komplain_baru > 0): ?>
          <span class="admin-nav-badge"><?= $komplain_baru ?></span>
          <?php endif; ?>
        </a>
        <a href="admin.php?page=transaksi" class="admin-nav-item <?= $activePage==='transaksi'?'active':'' ?>">
          <span class="ani">💳</span> Transaksi
          <?php if($belum_bayar > 0): ?>
          <span class="admin-nav-badge admin-nav-badge-warn"><?= $belum_bayar ?></span>
          <?php endif; ?>
        </a>
        <!-- ===== MENU PESAN ===== -->
        <a href="admin.php?page=pesan" class="admin-nav-item <?= $activePage==='pesan'?'active':'' ?>">
          <span class="ani">📩</span> Pesan
          <?php if($pesan_baru > 0): ?>
          <span class="admin-nav-badge" style="background:#dc2626;"><?= $pesan_baru ?></span>
          <?php endif; ?>
        </a>
      </div>
    </nav>
    
    <div class="admin-sidebar-footer">
      <div class="admin-user-card">
        <div class="admin-user-avatar">
          <?= strtoupper(substr($_SESSION['user_nama'] ?? 'A', 0, 1)) ?>
        </div>
        <div style="overflow:hidden;">
          <div class="admin-user-name"><?= htmlspecialchars($_SESSION['user_nama'] ?? 'Admin') ?></div>
          <div class="admin-user-role">👑 Administrator</div>
        </div>
      </div>
      <a href="admin.php?action=logout" class="admin-logout-btn">
        🚪 Keluar
      </a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <div style="display:flex;align-items:center;gap:12px;">
        <button class="hamburger" onclick="openSidebar()" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
        <span class="admin-topbar-title"><?= $pageTitle ?? 'Dashboard' ?></span>
      </div>
      <div class="admin-topbar-right">
        <span class="badge badge-admin">👑 Admin</span>
        <span style="font-size:12px;color:var(--text-muted);">
          <?= htmlspecialchars(explode(' ', $_SESSION['user_nama'] ?? 'Admin')[0]) ?>
        </span>
      </div>
    </header>

    <main class="admin-page-content">
<?php
// Toast
$toast_msg  = $_SESSION['toast_msg']  ?? null;
$toast_type = $_SESSION['toast_type'] ?? '';
unset($_SESSION['toast_msg'], $_SESSION['toast_type']);
?>