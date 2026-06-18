<?php
$kamar_all     = getKamarData();
$komplain_all  = getKomplainAll();
$transaksi_all = getTransaksiAll();

$total_kamar   = count($kamar_all);
$tersedia      = count(array_filter($kamar_all, fn($k) => $k['status'] === 'tersedia'));
$terisi        = $total_kamar - $tersedia;

$komplain_baru = count(array_filter($komplain_all, fn($k) => $k['status'] === 'baru'));
$komplain_pros = count(array_filter($komplain_all, fn($k) => $k['status'] === 'diproses'));
$komplain_done = count(array_filter($komplain_all, fn($k) => $k['status'] === 'selesai'));

$lunas         = array_filter($transaksi_all, fn($t) => $t['status'] === 'lunas');
$belum_bayar   = array_filter($transaksi_all, fn($t) => $t['status'] === 'belum_bayar');
$menunggu      = array_filter($transaksi_all, fn($t) => $t['status'] === 'menunggu_konfirmasi');
$pending       = array_filter($transaksi_all, fn($t) => $t['status'] === 'pending');
$total_pendapatan = array_sum(array_column(array_values($lunas), 'jumlah'));

$users_all = getDemoData('users');
$total_penghuni = count(array_filter($users_all, fn($u) => $u['role'] === 'penghuni'));
?>

<!-- Stats Row 1 -->
<div class="admin-stats-grid">
  <div class="admin-stat-card accent">
    <div class="admin-stat-icon">🏠</div>
    <div class="admin-stat-val"><?= $total_kamar ?></div>
    <div class="admin-stat-lbl">Total Kamar</div>
    <div class="admin-stat-sub"><?= $terisi ?> terisi · <?= $tersedia ?> tersedia</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">👩</div>
    <div class="admin-stat-val"><?= $terisi ?></div>
    <div class="admin-stat-lbl">Penghuni Aktif</div>
    <div class="admin-stat-sub"><?= $tersedia ?> kamar kosong</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">📋</div>
    <div class="admin-stat-val"><?= count($komplain_all) ?></div>
    <div class="admin-stat-lbl">Total Komplain</div>
    <div class="admin-stat-sub"><?= $komplain_baru ?> baru · <?= $komplain_pros ?> diproses</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">💰</div>
    <div class="admin-stat-val" style="color:#0F6E56;">Rp <?= number_format($total_pendapatan/1000, 0, ',', '.') ?>K</div>
    <div class="admin-stat-lbl">Pendapatan Lunas</div>
    <div class="admin-stat-sub"><?= count($lunas) ?> transaksi lunas</div>
  </div>
</div>

<!-- Row 2: Quick Action + Status Kamar -->
<div class="admin-grid-2">

  <!-- Aksi Cepat -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">⚡ Aksi Cepat</span>
    </div>
    <div class="admin-card-body" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px,1fr));gap:.75rem;">
      <?php
      $actions = [
        ['admin.php?page=komplain', '📋', 'Kelola Komplain', $komplain_baru.' baru', $komplain_baru>0?'#dc2626':'#0F6E56'],
        ['admin.php?page=transaksi', '💳', 'Kelola Transaksi', count($belum_bayar).' belum bayar', count($belum_bayar)>0?'#d97706':'#0F6E56'],
        ['admin.php?page=kamar', '🏠', 'Kelola Kamar', $tersedia.' tersedia', '#0F6E56'],
        ['admin.php?page=penghuni', '👩', 'Data Penghuni', $terisi.' aktif', '#0F6E56'],
      ];
      foreach($actions as $a):
      ?>
      <a href="<?= $a[0] ?>" style="text-decoration:none;">
        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:1rem;text-align:center;transition:border-color .2s;"
             onmouseover="this.style.borderColor='<?= $a[4] ?>'" onmouseout="this.style.borderColor='#e5e7eb'">
          <div style="font-size:1.5rem;margin-bottom:5px;"><?= $a[1] ?></div>
          <div style="font-weight:700;font-size:12px;color:#1a1a1a;"><?= $a[2] ?></div>
          <div style="font-size:10px;color:<?= $a[4] ?>;margin-top:3px;"><?= $a[3] ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Status Kamar Visual -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">🏠 Status Hunian Kamar</span>
      <a href="admin.php?page=kamar" class="admin-btn admin-btn-outline admin-btn-sm">Kelola →</a>
    </div>
    <div class="admin-card-body">
      <div style="margin-bottom:1rem;">
        <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
          <span style="font-size:11px;color:#475569;">Tingkat Hunian</span>
          <span style="font-size:11px;font-weight:700;color:#0F6E56;"><?= round($terisi/$total_kamar*100) ?>%</span>
        </div>
        <div style="background:#e5e7eb;border-radius:9px;height:10px;overflow:hidden;">
          <div style="background:linear-gradient(90deg,#0F6E56,#4ade80);height:100%;width:<?= round($terisi/$total_kamar*100) ?>%;border-radius:9px;transition:width .5s;"></div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:5px;">
        <?php foreach($kamar_all as $k): ?>
        <div title="<?= $k['nama'] ?> — <?= ucfirst($k['status']) ?>"
             style="aspect-ratio:1;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;cursor:default;
             <?= $k['status']==='terisi' ? 'background:#0F6E56;color:white;' : 'background:#e5e7eb;color:#475569;border:1px dashed #94a3b8;' ?>">
          <?= preg_replace('/[^A-Z0-9]/i', '', $k['nama']) ?>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:12px;margin-top:1rem;font-size:11px;">
        <span><span style="display:inline-block;width:10px;height:10px;background:#0F6E56;border-radius:3px;margin-right:4px;"></span>Terisi (<?= $terisi ?>)</span>
        <span><span style="display:inline-block;width:10px;height:10px;background:#e5e7eb;border:1px dashed #94a3b8;border-radius:3px;margin-right:4px;"></span>Tersedia (<?= $tersedia ?>)</span>
      </div>
    </div>
  </div>

</div>

<!-- Row 3: Komplain terbaru + Transaksi belum bayar -->
<div class="admin-grid-2">

  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">📋 Komplain Terbaru</span>
      <a href="admin.php?page=komplain" class="admin-btn admin-btn-outline admin-btn-sm">Lihat Semua</a>
    </div>
    <?php $recent = array_slice($komplain_all, 0, 5); ?>
    <?php if(empty($recent)): ?>
    <div class="admin-empty"><span class="admin-empty-icon">📭</span>Belum ada komplain</div>
    <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th style="color:#475569;">Tiket</th><th style="color:#475569;">Kamar</th><th style="color:#475569;">Kategori</th><th style="color:#475569;">Status</th></tr></thead>
        <tbody>
        <?php foreach($recent as $k):
          $bdg = ['baru'=>'admin-badge-baru','diproses'=>'admin-badge-diproses','selesai'=>'admin-badge-selesai'];
          $lbl = ['baru'=>'Baru','diproses'=>'Diproses','selesai'=>'Selesai'];
        ?>
        <tr>
          <td style="font-family:monospace;font-size:10px;font-weight:700;color:#475569;"><?= $k['no_tiket'] ?></td>
          <td style="font-weight:700;color:#0F6E56;"><?= $k['no_kamar'] ?></td>
          <td style="color:#475569;font-size:11px;"><?= $k['kategori'] ?></td>
          <td><span class="admin-badge <?= $bdg[$k['status']] ?? '' ?>"><?= $lbl[$k['status']] ?? $k['status'] ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">💳 Tagihan Belum Lunas</span>
      <a href="admin.php?page=transaksi" class="admin-btn admin-btn-outline admin-btn-sm">Lihat Semua</a>
    </div>
    <?php $tagihan = array_values($belum_bayar); ?>
    <?php if(empty($tagihan)): ?>
    <div class="admin-empty"><span class="admin-empty-icon">✅</span>Semua tagihan sudah lunas!</div>
    <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th style="color:#475569;">Nama</th><th style="color:#475569;">Kamar</th><th style="color:#475569;">Bulan</th><th style="color:#475569;">Jumlah</th></tr></thead>
        <tbody>
        <?php foreach(array_slice($tagihan, 0, 5) as $t): ?>
        <tr>
          <td style="font-weight:600;color:#1a1a1a;"><?= htmlspecialchars($t['nama']) ?></td>
          <td style="color:#0F6E56;font-weight:700;"><?= $t['no_kamar'] ?></td>
          <td style="color:#475569;font-size:11px;"><?= $t['bulan'] ?></td>
          <td style="color:#b91c1c;font-weight:700;">Rp <?= number_format($t['jumlah'],0,',','.') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div>