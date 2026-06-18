<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? $pageTitle . ' — ' : '' ?>Kost Putri Capoeng</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php
$loggedIn   = isLoggedIn();
$login_err  = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>

<!-- ===== MODAL: Login Required Pop-up ===== -->
<div class="modal-overlay" id="loginModal">
  <div class="modal-box">
    <div class="modal-icon">🔒</div>
    <div class="modal-title">Login Diperlukan</div>
    <div class="modal-desc">Fitur ini hanya tersedia untuk penghuni terdaftar.<br>Silakan login dengan akun kamu.</div>
    <div style="margin-bottom:1rem;">
      <?php if ($login_err): ?>
      <div class="alert alert-error" style="text-align:left;"><?= $login_err ?></div>
      <?php endif; ?>
      <form method="post" id="modalLoginForm">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="current_page" value="<?= htmlspecialchars($activePage) ?>">
        <input type="hidden" name="redirect" id="modal_redirect" value="<?= htmlspecialchars($activePage) ?>">
        <div class="form-group" style="margin-bottom:.75rem;">
          <input type="email" name="email" placeholder="Email" required style="background:white;">
        </div>
        <div class="form-group" style="margin-bottom:.75rem;">
          <input type="password" name="password" placeholder="Password" required style="background:white;">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-bottom:.5rem;">Masuk →</button>
      </form>
    </div>
    <div class="modal-actions">
      <button onclick="closeLoginModal()" class="btn btn-outline" style="flex:1;">Kembali</button>
    </div>

  </div>
</div>

<!-- ===== MODAL: Booking / Pesan Kamar ===== -->
<div class="modal-overlay" id="bookingModal">
  <div class="modal-box" style="max-width:440px;text-align:left;">
    <div style="font-weight:700;font-size:15px;margin-bottom:.25rem;" id="bookingKamarName">Pesan Kamar</div>
    <div style="font-size:12px;color:var(--text-muted);margin-bottom:1.25rem;" id="bookingKamarHarga"></div>
    <?php if (!$loggedIn): ?>
    <div class="alert alert-warning" style="margin-bottom:1rem;">
      ⚠️ Kamu belum login. Booking akan disimpan sebagai tamu.
    </div>
    <?php endif; ?>
    <form method="post" id="bookingForm" action="index.php?page=transaksi">
      <input type="hidden" name="action" value="booking_kamar">
      <input type="hidden" name="kamar_id" id="booking_kamar_id">
      <input type="hidden" name="kamar_nama" id="booking_kamar_nama">
      <input type="hidden" name="kamar_harga" id="booking_kamar_harga">
      <div class="form-row">
        <div class="form-group">
          <label>Nama Lengkap *</label>
          <input type="text" name="nama" value="<?= htmlspecialchars($_SESSION['user_nama'] ?? '') ?>" placeholder="Nama lengkap" required>
        </div>
        <div class="form-group">
          <label>No. WhatsApp *</label>
          <input type="text" name="no_hp" value="<?= htmlspecialchars($_SESSION['user_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx" required>
        </div>
      </div>
      <div class="form-group">
        <label>Periode Sewa (Mulai Bulan) *</label>
        <input type="month" name="periode" required>
      </div>
      <div class="form-group">
        <label>Metode Pembayaran *</label>
        <select name="metode" required>
          <option value="">— Pilih metode —</option>
          <option>Transfer Bank (BRI)</option>
          <option>Transfer Bank (BCA)</option>
          <option>QRIS</option>
          <option>GoPay</option>
          <option>OVO</option>
          <option>Dana</option>
          <option>Tunai</option>
        </select>
      </div>
      <div style="background:var(--teal-light);border-radius:8px;padding:10px 12px;font-size:11px;color:var(--teal-dark);margin-bottom:1rem;">
        <strong>Rekening BRI:</strong> 1234-5678-9012-3456 a.n. Lukyta Surawijaya<br>
        <span style="opacity:.8;">Konfirmasi pembayaran via WhatsApp: 0812-3456-7890</span>
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeBookingModal()" class="btn btn-outline" style="flex:1;">Batal</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">📅 Konfirmasi Booking</button>
      </div>
    </form>
  </div>
</div>

<div class="app-layout">
  <!-- Overlay mobile -->
  <div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>

  <!-- ===== SIDEBAR ===== -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-brand">
        <div class="sidebar-logo">KC</div>
        <div>
          <div class="sidebar-name">Kost Putri Capoeng</div>
          <div class="sidebar-sub">Banyuwangi, Jawa Timur</div>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <!-- Menu publik — semua bisa akses -->
      <div class="nav-section">
        <div class="nav-section-title">Menu Utama</div>

        <a href="index.php?page=beranda" class="nav-item <?= ($activePage==='beranda')?'active':'' ?>">
          <span class="nav-icon">🏠</span> Beranda
        </a>
        <a href="index.php?page=kamar" class="nav-item <?= ($activePage==='kamar')?'active':'' ?>">
          <span class="nav-icon">🛏️</span> Katalog Kamar
        </a>
        <a href="index.php?page=kontak" class="nav-item <?= ($activePage==='kontak')?'active':'' ?>">
          <span class="nav-icon">📞</span> Kontak
        </a>
      </div>

      <!-- Menu terlindungi -->
      <div class="nav-section">
        <div class="nav-section-title">Penghuni</div>

        <?php if ($loggedIn): ?>
          <a href="index.php?page=komplain" class="nav-item <?= ($activePage==='komplain')?'active':'' ?>">
            <span class="nav-icon">📋</span> Komplain
            <?php
              $jml_baru = count(array_filter(getKomplainAll(), fn($k) => $k['status']==='baru'));
              if ($jml_baru > 0):
            ?>
            <span class="nav-badge"><?= $jml_baru ?></span>
            <?php endif; ?>
          </a>
          <a href="index.php?page=transaksi" class="nav-item <?= ($activePage==='transaksi')?'active':'' ?>">
            <span class="nav-icon">💳</span> Transaksi
          </a>
          <a href="index.php?page=profil" class="nav-item <?= ($activePage==='profil')?'active':'' ?>">
            <span class="nav-icon">👤</span> Data Diri
          </a>
        <?php else: ?>
          <!-- Tamu: item dikunci, klik buka modal login -->
          <a href="#" class="nav-item nav-locked" onclick="requireLogin('komplain'); return false;">
            <span class="nav-icon">📋</span> Komplain
            <span class="nav-lock-icon">🔒</span>
          </a>
          <a href="#" class="nav-item nav-locked" onclick="requireLogin('transaksi'); return false;">
            <span class="nav-icon">💳</span> Transaksi
            <span class="nav-lock-icon">🔒</span>
          </a>
          <a href="#" class="nav-item nav-locked" onclick="requireLogin('profil'); return false;">
            <span class="nav-icon">👤</span> Data Diri
            <span class="nav-lock-icon">🔒</span>
          </a>
        <?php endif; ?>
      </div>
    </nav>

    <!-- ===== FOOTER SIDEBAR ===== -->
    <?php if ($loggedIn): ?>
    <!-- Sudah login: tampilkan profil + logout -->
    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_nama'] ?? 'U', 0, 1)) ?></div>
        <div style="overflow:hidden;">
          <div class="user-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            <?= htmlspecialchars($_SESSION['user_nama'] ?? 'Pengguna') ?>
          </div>
          <div class="user-role"><?= ucfirst($_SESSION['user_role'] ?? 'penghuni') ?></div>
        </div>
      </div>
      <a href="index.php?action=logout" class="logout-btn">
        <span>🚪</span> Keluar
      </a>
    </div>

    <?php else: ?>
    <!-- Tamu: panel login compact di bawah sidebar -->
    <div class="sidebar-guest-footer">
      <p>Sudah punya akun?</p>
      <?php if ($login_err): ?>
      <div class="alert alert-error" style="margin-bottom:.6rem;font-size:11px;"><?= $login_err ?></div>
      <?php endif; ?>
      <form method="post" style="margin-bottom:0;">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="current_page" value="<?= htmlspecialchars($activePage) ?>">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($activePage) ?>">
        <div style="margin-bottom:6px;">
          <input type="email" name="email" placeholder="Email"
            style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit;outline:none;background:white;">
        </div>
        <div style="margin-bottom:8px;">
          <input type="password" name="password" placeholder="Password"
            style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit;outline:none;background:white;">
        </div>
        <button type="submit" class="btn btn-primary"
          style="width:100%;padding:8px;font-size:12px;border-radius:8px;">
          Masuk →
        </button>
      </form>

    </div>
    <?php endif; ?>
  </aside>

  <!-- ===== MAIN CONTENT ===== -->
  <div class="main-content">
    <header class="topbar">
      <div class="flex items-center gap-2">
        <button class="hamburger" onclick="openSidebar()" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
        <span class="topbar-title"><?= $pageTitle ?? 'Dashboard' ?></span>
      </div>
      <div class="topbar-right">
        <?php if ($loggedIn): ?>
          <span class="badge <?= isAdmin() ? 'badge-admin' : 'badge-penghuni' ?>">
            <?= isAdmin() ? '👑 Admin' : '🏠 Penghuni' ?>
          </span>
          <span style="font-size:12px;color:var(--text-muted);">
            <?= htmlspecialchars(explode(' ', $_SESSION['user_nama'] ?? 'Pengguna')[0]) ?>
          </span>
        <?php else: ?>
          <span class="badge badge-tamu">👤 Tamu</span>
          <a href="#" onclick="openLoginModal('beranda'); return false;" class="topbar-login-btn">
            🔑 Login
          </a>
        <?php endif; ?>
      </div>
    </header>

    <main class="page-content">
