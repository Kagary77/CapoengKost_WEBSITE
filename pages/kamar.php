<?php
$kamar_data = getKamarData();
$filter = $_GET['filter'] ?? 'semua';
if ($filter === 'standard') $kamar_data = array_filter($kamar_data, fn($k) => $k['tipe']==='Standard');
elseif ($filter === 'premium') $kamar_data = array_filter($kamar_data, fn($k) => $k['tipe']==='Premium');
elseif ($filter === 'tersedia') $kamar_data = array_filter($kamar_data, fn($k) => $k['status']==='tersedia');
$loggedIn  = isLoggedIn();
$user_kamar = $_SESSION['user_kamar'] ?? null;
?>

<!-- Info Tamu -->
<?php if (!$loggedIn): ?>
<div class="alert alert-info mb-2" style="display:flex;align-items:center;gap:10px;">
  <span style="font-size:1.1rem;">ℹ️</span>
  <span>Kamu browsing sebagai tamu. Kamu bisa melihat katalog dan memesan kamar.
  <a href="#" onclick="openLoginModal('kamar');return false;" style="color:var(--teal-dark);font-weight:700;">Login</a> untuk akses fitur lengkap.</span>
</div>
<?php endif; ?>

<!-- Peringatan jika user sudah memiliki kamar -->
<?php if ($loggedIn && !empty($user_kamar) && $user_kamar !== '-'): ?>
<div class="alert alert-warning mb-2" style="display:flex;align-items:center;gap:10px;">
  <span style="font-size:1.1rem;">ℹ️</span>
  <span>Kamu sudah memiliki kamar <strong><?= $user_kamar ?></strong>. Tidak bisa memesan kamar lain.</span>
</div>
<?php endif; ?>

<!-- Filter Bar -->
<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:1.25rem;">
  <div style="display:flex; gap:6px; flex-wrap:wrap;">
    <?php foreach(['semua'=>'Semua','standard'=>'Standard','premium'=>'Premium','tersedia'=>'Tersedia'] as $val=>$lbl): ?>
    <a href="index.php?page=kamar&filter=<?= $val ?>"
       class="btn <?= $filter===$val ? 'btn-primary' : 'btn-outline' ?> btn-sm">
      <?= $lbl ?>
    </a>
    <?php endforeach; ?>
  </div>
  <span class="text-sm text-muted"><?= count($kamar_data) ?> kamar ditemukan</span>
</div>

<!-- Kamar Grid -->
<?php if (empty($kamar_data)): ?>
<div class="empty-state">
  <span class="empty-icon">🔍</span><h4>Tidak ada kamar</h4><p>Coba filter lain</p>
</div>
<?php else: ?>
<div class="kamar-grid">
  <?php foreach($kamar_data as $k): ?>
  <div class="kamar-card">
    <div class="kamar-thumb kamar-thumb-<?= $k['tipe']==='Premium'?'prm':'std' ?>">
      <span><?= $k['tipe']==='Premium' ? '🏠' : '🛏️' ?></span>
      <span class="badge badge-<?= strtolower($k['tipe']) ?>" style="position:absolute;top:10px;left:10px;"><?= $k['tipe'] ?></span>
      <span class="badge badge-<?= $k['status']==='tersedia'?'tersedia':'terisi' ?>" style="position:absolute;top:10px;right:10px;">
        <?= $k['status']==='tersedia'?'Tersedia':'Terisi' ?>
      </span>
    </div>
    <div class="kamar-body">
      <div class="kamar-name"><?= $k['nama'] ?></div>
      <div class="kamar-harga">Rp <?= number_format($k['harga'],0,',','.') ?> <span>/ bulan</span></div>
      <div class="kamar-fasilitas">
        <?php foreach(explode(', ', $k['fasilitas']) as $f): ?>
        <span class="tag"><?= trim($f) ?></span>
        <?php endforeach; ?>
      </div>

      <?php if($k['status']==='tersedia'): ?>
        <?php if ($loggedIn && !empty($user_kamar) && $user_kamar !== '-'): ?>
          <!-- User sudah punya kamar → tombol disabled -->
          <div style="background:var(--bg);color:var(--text-light);text-align:center;cursor:not-allowed;border:1px solid var(--border);border-radius:8px;padding:8px;font-size:13px;font-weight:600;">
            🔒 Kamu sudah punya kamar
          </div>
        <?php else: ?>
          <button onclick="openBookingModal(<?= $k['id'] ?>, '<?= htmlspecialchars($k['nama']) ?>', <?= $k['harga'] ?>)"
            class="btn btn-primary" style="width:100%;display:block;text-align:center;border-radius:8px;">
            📅 Pesan Kamar Ini
          </button>
        <?php endif; ?>
      <?php else: ?>
        <div style="background:var(--bg);color:var(--text-light);text-align:center;cursor:default;border:1px solid var(--border);border-radius:8px;padding:8px;font-size:13px;font-weight:600;">
          Kamar Sedang Terisi
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Tabel Perbandingan -->
<div class="card mt-3">
  <div class="card-header"><span class="card-title">📊 Perbandingan Tipe Kamar</span></div>
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Fasilitas</th>
            <th style="text-align:center;">Standard</th>
            <th style="text-align:center;">Premium ✦</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $komparasi = [
            'AC'                  => [false, true],
            'WiFi'                => [true, true],
            'Kamar Mandi Dalam'   => [true, true],
            'Lemari'              => [true, true],
            'Meja Belajar'        => [true, true],
            'Area Parkir'         => [true, true],
            'Area Jemur Bersama'  => [true, true],
          ];
          foreach($komparasi as $item => [$std, $prm]):
          ?>
          <tr>
            <td><?= $item ?></td>
            <td style="text-align:center;"><?= $std ? '<span style="color:#166534;font-weight:700;">✓</span>' : '<span style="color:#9ca3af;">—</span>' ?></td>
            <td style="text-align:center;"><?= $prm ? '<span style="color:var(--teal);font-weight:700;">✓</span>' : '<span style="color:#9ca3af;">—</span>' ?></td>
          </tr>
          <?php endforeach; ?>
          <tr style="background:var(--bg);">
            <td style="font-weight:700;color:#1a1a1a;">Harga / Bulan</td>
            <td style="text-align:center;font-weight:700;color:#1a1a1a;">Rp 450.000</td>
            <td style="text-align:center;font-weight:700;color:var(--teal);">Rp 850.000</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- CTA Login untuk tamu -->
<?php if (!$loggedIn): ?>
<div class="card mt-3" style="background:linear-gradient(135deg,#04342C,#0F6E56);color:white;border:none;">
  <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
    <div>
      <div style="font-weight:700;font-size:14px;margin-bottom:4px;">Sudah punya akun?</div>
      <div style="font-size:12px;opacity:.8;">Login untuk akses komplain, riwayat transaksi, dan data diri.</div>
    </div>
    <a href="#" onclick="openLoginModal('beranda');return false;"
       style="background:white;color:var(--teal-dark);padding:9px 22px;border-radius:8px;font-weight:700;font-size:13px;text-decoration:none;flex-shrink:0;">
      🔑 Login Sekarang
    </a>
  </div>
</div>
<?php endif; ?>