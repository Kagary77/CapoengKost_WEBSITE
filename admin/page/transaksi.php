<?php
$transaksi_all = getTransaksiAll();
$filter_status = $_GET['status'] ?? 'semua';

$filtered = $transaksi_all;
if ($filter_status !== 'semua') {
    $filtered = array_filter($transaksi_all, fn($t) => $t['status'] === $filter_status);
}

$lunas       = array_filter($transaksi_all, fn($t) => $t['status']==='lunas');
$belum       = array_filter($transaksi_all, fn($t) => $t['status']==='belum_bayar');
$pending     = array_filter($transaksi_all, fn($t) => $t['status']==='pending');
$total_lunas = array_sum(array_column(array_values($lunas), 'jumlah'));
$total_belum = array_sum(array_column(array_values($belum), 'jumlah'));

$kamar_data = getKamarData();

$sdg = [
    'lunas' => 'admin-badge-lunas',
    'belum_bayar' => 'admin-badge-belum',
    'pending' => 'admin-badge-pending'
];
$slb = [
    'lunas' => 'Lunas',
    'belum_bayar' => 'Belum Bayar',
    'pending' => 'Pending'
];
?>

<div class="admin-stats-grid" style="grid-template-columns:repeat(4,1fr);">
  <div class="admin-stat-card accent">
    <div class="admin-stat-icon">💰</div>
    <div class="admin-stat-val" style="color:#0F6E56;">Rp <?= number_format($total_lunas/1000,0,',','.') ?>K</div>
    <div class="admin-stat-lbl">Total Lunas</div>
    <div class="admin-stat-sub"><?= count($lunas) ?> transaksi</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">⏳</div>
    <div class="admin-stat-val"><?= count($belum) ?></div>
    <div class="admin-stat-lbl">Belum Bayar</div>
    <div class="admin-stat-sub">Rp <?= number_format($total_belum/1000,0,',','.') ?>K tertunggak</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">⏳</div>
    <div class="admin-stat-val"><?= count($pending) ?></div>
    <div class="admin-stat-lbl">Menunggu Validasi</div>
    <div class="admin-stat-sub">Bukti perlu dicek</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">📊</div>
    <div class="admin-stat-val"><?= count($transaksi_all) ?></div>
    <div class="admin-stat-lbl">Total Transaksi</div>
  </div>
</div>

<div class="admin-grid-2" style="align-items:start;">

  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">💳 Daftar Transaksi</span>
      <div style="display:flex;gap:5px;flex-wrap:wrap;">
        <?php foreach(['semua'=>'Semua','lunas'=>'Lunas','belum_bayar'=>'Belum','pending'=>'Pending'] as $v=>$l): ?>
        <a href="admin.php?page=transaksi&status=<?= $v ?>"
           class="admin-btn admin-btn-sm <?= $filter_status===$v?'admin-btn-primary':'admin-btn-outline' ?>"><?= $l ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if(empty($filtered)): ?>
    <div class="admin-empty"><span class="admin-empty-icon">📭</span>Tidak ada transaksi</div>
    <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table admin-table-transaksi">
        <thead>
          <tr>
            <th style="color:#475569;">Nama</th>
            <th style="color:#475569;">Kamar</th>
            <th style="color:#475569;">Bulan</th>
            <th style="color:#475569;">Jumlah</th>
            <th style="color:#475569;">Metode</th>
            <th style="color:#475569;">Status</th>
            <th style="color:#475569;">Bukti</th>
            <th style="color:#475569;min-width:120px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($filtered as $t): ?>
        <tr>
          <td style="font-weight:700;color:#1a1a1a;white-space:nowrap;"><?= htmlspecialchars($t['nama']) ?></td>
          <td style="font-weight:700;color:#0F6E56;"><?= $t['no_kamar'] ?></td>
          <td style="font-size:11px;color:#475569;white-space:nowrap;"><?= $t['bulan'] ?></td>
          <td style="font-weight:700;color:<?= $t['status']==='lunas'?'#0F6E56':'#b91c1c' ?>;white-space:nowrap;">
            Rp <?= number_format($t['jumlah'],0,',','.') ?>
          </td>
          <td style="font-size:11px;color:#475569;"><?= $t['metode'] ?></td>
          <td><span class="admin-badge <?= $sdg[$t['status']] ?? '' ?>"><?= $slb[$t['status']] ?? $t['status'] ?></span></td>
          <td>
            <?php if ($t['bukti']): ?>
              <a href="uploads/pembayaran/<?= $t['bukti'] ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm" title="Lihat Bukti">📷</a>
            <?php else: ?>
              <span style="color:#94a3b8;">-</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;flex-wrap:wrap;gap:4px;align-items:center;justify-content:flex-start;">
              <?php if ($t['status'] === 'pending'): ?>
                <form method="post" style="display:flex;gap:3px;">
                  <input type="hidden" name="action" value="validasi_transaksi">
                  <input type="hidden" name="id" value="<?= $t['id'] ?>">
                  <button type="submit" name="status" value="lunas" class="admin-btn admin-btn-success admin-btn-sm" title="Setujui">✔</button>
                  <button type="submit" name="status" value="belum_bayar" class="admin-btn admin-btn-danger admin-btn-sm" title="Tolak">✖</button>
                </form>
              <?php endif; ?>
              <form method="post" style="display:inline;" onsubmit="return confirm('Hapus transaksi ini?')">
                <input type="hidden" name="action" value="hapus_transaksi">
                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" style="height:26px;line-height:1;">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">➕ Buat Tagihan Baru</span>
    </div>
    <div class="admin-card-body">
      <form method="post">
        <input type="hidden" name="action" value="tambah_tagihan">
        <div class="admin-form-group">
          <label style="color:#1e293b;">Nama Penghuni *</label>
          <input type="text" name="nama" placeholder="Nama penghuni" required>
        </div>
        <div class="admin-form-row">
          <div class="admin-form-group">
            <label style="color:#1e293b;">No. Kamar *</label>
            <select name="no_kamar" required>
              <option value="">— Pilih kamar —</option>
              <?php foreach($kamar_data as $k): ?>
              <option value="<?= $k['nama'] ?>">
                <?= $k['nama'] ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="admin-form-group">
            <label style="color:#1e293b;">Bulan Tagihan *</label>
            <input type="month" name="bulan" required>
            <span style="font-size:10px;color:#94a3b8;">Pilih bulan &amp; tahun tagihan</span>
          </div>
        </div>
        <div class="admin-form-row">
          <div class="admin-form-group">
            <label style="color:#1e293b;">Jumlah (Rp) *</label>
            <input type="number" name="jumlah" placeholder="450000" required>
          </div>
          <div class="admin-form-group">
            <label style="color:#1e293b;">Metode Pembayaran</label>
            <select name="metode">
              <option value="-">Belum dipilih</option>
              <option>Transfer Bank (BRI)</option>
              <option>Transfer Bank (BCA)</option>
              <option>QRIS</option>
              <option>GoPay</option>
              <option>OVO</option>
              <option>Dana</option>
              <option>Tunai</option>
            </select>
          </div>
        </div>
        <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;">➕ Buat Tagihan</button>
        <div style="margin-top:.5rem;font-size:10px;color:#94a3b8;text-align:center;">
          ⚡ Status akan otomatis <strong>Pending</strong> dan menunggu validasi admin.
        </div>
      </form>

      <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid #e5e7eb;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#475569;margin-bottom:.75rem;">📊 Ringkasan Bulan Ini</div>
        <?php
        $trans_bulan = array_filter($transaksi_all, function($t) {
            return strpos($t['bulan'], date('Y')) !== false;
        });
        $lunas_bulan = array_filter($trans_bulan, fn($t) => $t['status']==='lunas');
        $pendapatan  = array_sum(array_column(array_values($lunas_bulan), 'jumlah'));
        ?>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e5e7eb;font-size:12px;">
          <span style="color:#475569;">Tagihan lunas</span>
          <span style="color:#0F6E56;font-weight:700;"><?= count($lunas_bulan) ?> transaksi</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:12px;">
          <span style="color:#475569;">Total terkumpul</span>
          <span style="color:#0F6E56;font-weight:700;">Rp <?= number_format($pendapatan,0,',','.') ?></span>
        </div>
      </div>
    </div>
  </div>

</div>