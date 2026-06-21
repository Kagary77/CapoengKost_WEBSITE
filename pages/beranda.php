<?php
$kamar_data    = getKamarData();
$tersedia      = count(array_filter($kamar_data, fn($k) => $k['status'] === 'tersedia'));
$komplain_all  = getKomplainAll();
$komplain_baru = count(array_filter($komplain_all, fn($k) => $k['status'] === 'baru'));

// ===== PERBAIKAN: Statistik transaksi dengan status baru =====
$transaksi_all = getTransaksiAll();

// Hitung transaksi berdasarkan status baru
$terkirim_count = count(array_filter($transaksi_all, fn($t) => $t['status'] === 'terkirim'));
$pending_count  = count(array_filter($transaksi_all, fn($t) => $t['status'] === 'pending'));
$lunas_count    = count(array_filter($transaksi_all, fn($t) => $t['status'] === 'lunas'));
$gagal_count    = count(array_filter($transaksi_all, fn($t) => $t['status'] === 'gagal'));

// Total tagihan yang belum lunas (terkirim + pending + gagal)
$belum_lunas_total = $terkirim_count + $pending_count + $gagal_count;

$nama_user = $_SESSION['user_nama'] ?? null;
$loggedIn  = isLoggedIn();
?>

<!-- Hero Banner -->
<div class="beranda-hero">
  <?php if ($loggedIn): ?>
    <h2>Selamat Datang, <?= htmlspecialchars(explode(' ', $nama_user)[0]) ?>! 👋</h2>
    <p>Kelola hunian, ajukan komplain, dan pantau pembayaran sewa kamu langsung dari sini.</p>
  <?php else: ?>
    <h2>Hunian Nyaman Khusus Wanita 🏠</h2>
    <p>Temukan kamar impianmu di Kost Putri Capoeng — fasilitas lengkap, lokasi strategis di jantung Banyuwangi.</p>
    <div class="hero-cta">
      <a href="index.php?page=kamar" class="hero-cta-primary">🛏️ Lihat Katalog Kamar</a>
      <a href="#" onclick="openLoginModal('beranda'); return false;" class="hero-cta-secondary">🔑 Login Akun</a>
    </div>
  <?php endif; ?>
  <div class="hero-badges">
    <span class="hero-badge-item">🏠 Kost Khusus Wanita</span>
    <span class="hero-badge-item">📍 Banyuwangi, Jawa Timur</span>
    <span class="hero-badge-item">⭐ Sejak 2010</span>
    <span class="hero-badge-item">✅ <?= $tersedia ?> Kamar Tersedia</span>
  </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon">🏠</div>
    <div class="stat-val"><?= count($kamar_data) ?></div>
    <div class="stat-lbl">Total Kamar</div>
    <div class="stat-sub"><?= $tersedia ?> tersedia</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon">✅</div>
    <div class="stat-val"><?= $tersedia ?></div>
    <div class="stat-lbl">Kamar Tersedia</div>
    <div class="stat-sub">Langsung bisa sewa</div>
  </div>
  <?php if ($loggedIn): ?>
  <div class="stat-card">
    <div class="stat-icon">📋</div>
    <div class="stat-val"><?= count($komplain_all) ?></div>
    <div class="stat-lbl">Total Komplain</div>
    <div class="stat-sub"><?= $komplain_baru ?> baru masuk</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon">💳</div>
    <div class="stat-val"><?= $lunas_count ?></div>
    <div class="stat-lbl">Transaksi Lunas</div>
    <div class="stat-sub"><?= $belum_lunas_total ?> belum lunas</div>
  </div>
  <?php else: ?>
  <div class="stat-card">
    <div class="stat-icon">💰</div>
    <div class="stat-val">450K</div>
    <div class="stat-lbl">Mulai Dari</div>
    <div class="stat-sub">per bulan</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon">🔒</div>
    <div class="stat-val">24/7</div>
    <div class="stat-lbl">Keamanan</div>
    <div class="stat-sub">Lingkungan aman</div>
  </div>
  <?php endif; ?>
</div>

<!-- Grid 2 kolom -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px,1fr)); gap:1.25rem;">

  <!-- Kamar Tersedia -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">🛏️ Kamar Tersedia</span>
      <a href="index.php?page=kamar" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    <div class="card-body" style="padding:0;">
      <table>
        <thead><tr><th>Kamar</th><th>Tipe</th><th>Harga</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach(array_slice(array_values(array_filter($kamar_data, fn($k)=>$k['status']==='tersedia')), 0, 4) as $k): ?>
        <tr>
          <td><strong><?= $k['nama'] ?></strong></td>
          <td><span class="badge badge-<?= strtolower($k['tipe']) ?>"><?= $k['tipe'] ?></span></td>
          <td class="text-teal" style="font-weight:600;">Rp <?= number_format($k['harga'],0,',','.') ?></td>
          <td><span class="badge badge-tersedia">Tersedia</span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Komplain (login) atau CTA booking (tamu) -->
  <?php if ($loggedIn): ?>
  <div class="card">
    <div class="card-header">
      <span class="card-title">📋 Komplain Terbaru</span>
      <a href="index.php?page=komplain" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    <div class="card-body" style="padding:0;">
      <?php if (empty($komplain_all)): ?>
      <div class="empty-state"><span class="empty-icon">📭</span><p>Belum ada komplain</p></div>
      <?php else: ?>
      <table>
        <thead><tr><th>No. Tiket</th><th>Kamar</th><th>Kategori</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach(array_slice($komplain_all, 0, 3) as $k): ?>
        <tr>
          <td style="font-family:monospace;font-size:11px;font-weight:600;"><?= $k['no_tiket'] ?></td>
          <td><?= $k['no_kamar'] ?></td>
          <td class="text-sm text-muted"><?= $k['kategori'] ?></td>
          <td>
            <?php $badge=['baru'=>'badge-baru','diproses'=>'badge-diproses','selesai'=>'badge-selesai']; $label=['baru'=>'Baru','diproses'=>'Diproses','selesai'=>'Selesai']; ?>
            <span class="badge <?= $badge[$k['status']] ?? '' ?>"><?= $label[$k['status']] ?? $k['status'] ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
  <?php else: ?>
  <!-- Tamu: tampilkan CTA booking & info -->
  <div class="card" style="background:linear-gradient(145deg,var(--teal-xlight),white);">
    <div class="card-header" style="border-bottom:1px solid #9FE1CB;">
      <span class="card-title" style="color:var(--teal-dark);">🌟 Kenapa Pilih Kost Ini?</span>
    </div>
    <div class="card-body">
      <?php
      $keunggulan = [
        ['✅','WiFi Gratis','Akses internet cepat tanpa biaya tambahan'],
        ['🚿','Kamar Mandi Dalam','Privasi terjaga di setiap kamar'],
        ['❄️','AC (Premium)','Kamar sejuk sepanjang hari'],
        ['🔒','Keamanan 24 Jam','Lingkungan aman khusus wanita'],
        ['🅿️','Area Parkir Luas','Motor & sepeda aman dan mudah'],
        ['📋','Sistem Komplain','Laporan keluhan ditangani cepat'],
      ];
      foreach($keunggulan as $k):
      ?>
      <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--border);">
        <span style="font-size:1rem;flex-shrink:0;"><?= $k[0] ?></span>
        <div>
          <div style="font-weight:600;font-size:12px;color:#1a1a1a;"><?= $k[1] ?></div>
          <div style="font-size:11px;color:var(--text-muted);"><?= $k[2] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      <a href="index.php?page=kamar" class="btn btn-primary" style="width:100%;margin-top:1rem;display:block;text-align:center;">
        🛏️ Lihat & Pesan Kamar
      </a>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- Info Kost -->
<div class="card mt-3">
  <div class="card-header"><span class="card-title">ℹ️ Informasi Kost</span></div>
  <div class="card-body">
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:1rem;">
      <div>
        <div class="text-sm text-muted mb-1">Pengelola</div>
        <div style="font-weight:600;color:#1a1a1a;">Bpk. Lukyta Surawijaya</div>
        <div class="text-sm text-muted">& Ibu Ina</div>
      </div>
      <div>
        <div class="text-sm text-muted mb-1">Lokasi</div>
        <div style="font-weight:600;color:#1a1a1a;">Banyuwangi, Jawa Timur</div>
      </div>
      <div>
        <div class="text-sm text-muted mb-1">Harga Sewa</div>
        <div style="font-weight:600;color:#1a1a1a;">Standard: Rp 450.000/bln</div>
        <div class="text-sm text-muted">Premium (AC): Rp 850.000/bln</div>
      </div>
      <div>
        <div class="text-sm text-muted mb-1">Fasilitas Umum</div>
        <div style="font-weight:600;color:#1a1a1a;">WiFi · Parkir · Area Jemur</div>
        <div class="text-sm text-muted">Kamar Mandi Dalam · AC (Premium)</div>
      </div>
    </div>
  </div>
</div>