<?php
$user_kamar = strtoupper(trim($_SESSION['user_kamar'] ?? ''));
$user_nama  = $_SESSION['user_nama'] ?? '';
$user_id    = $_SESSION['user_id']   ?? 0;

$my_trans    = getMyTransaksi();
$lunas_list  = array_values(array_filter($my_trans, fn($t) => $t['status'] === 'lunas'));
$belum_list  = array_values(array_filter($my_trans, fn($t) => $t['status'] === 'belum_bayar'));
$pending_list = array_values(array_filter($my_trans, fn($t) => $t['status'] === 'pending'));
$total_masuk = array_sum(array_column($lunas_list, 'jumlah'));
$total_belum = array_sum(array_column($belum_list, 'jumlah'));
$booked = isset($_GET['booked']);
?>

<?php if ($booked): ?>
<div class="alert alert-success mb-3" style="display:flex;align-items:center;gap:12px;">
  <span style="font-size:1.4rem;">🎉</span>
  <div>
    <strong>Booking berhasil dikirim!</strong><br>
    <span style="font-size:12px;">Pengelola akan mengkonfirmasi dan menghubungi kamu via WhatsApp.</span>
  </div>
</div>
<?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:1.25rem;">
  <div>
    <div style="font-size:13px;font-weight:700;color:#1a1a1a;">
      <?php if (isAdmin()): ?>
        📊 Semua Transaksi Penghuni
      <?php else: ?>
        🔐 Transaksi Saya — Kamar <span style="color:var(--teal);"><?= $user_kamar ?: '-' ?></span>
      <?php endif; ?>
    </div>
    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
      <?php if (isAdmin()): ?>
        Menampilkan seluruh data transaksi semua penghuni.
      <?php else: ?>
        Hanya menampilkan transaksi milik akun kamu. Data penghuni lain bersifat privat.
      <?php endif; ?>
    </div>
  </div>
  <?php if (!isAdmin()): ?>
  <span style="background:var(--teal-light);color:var(--teal-dark);border:1px solid #9FE1CB;border-radius:8px;padding:5px 12px;font-size:11px;font-weight:600;">
    🔒 Mode Privat
  </span>
  <?php endif; ?>
</div>

<div class="trans-summary" style="margin-bottom:1.5rem;">
  <div class="trans-card">
    <div class="stat-icon">✅</div>
    <div class="stat-val" style="font-size:1.25rem;color:var(--teal);">
      Rp <?= number_format($total_masuk, 0, ',', '.') ?>
    </div>
    <div class="stat-lbl">Total Sudah Dibayar</div>
    <div class="stat-sub"><?= count($lunas_list) ?> transaksi lunas</div>
  </div>
  <div class="trans-card">
    <div class="stat-icon">⏳</div>
    <div class="stat-val" style="font-size:1.25rem;color:var(--coral);">
      Rp <?= number_format($total_belum, 0, ',', '.') ?>
    </div>
    <div class="stat-lbl">Total Belum Dibayar</div>
    <div class="stat-sub"><?= count($belum_list) ?> tagihan pending</div>
  </div>
  <div class="trans-card">
    <div class="stat-icon">📋</div>
    <div class="stat-val"><?= count($my_trans) ?></div>
    <div class="stat-lbl">Total Transaksi</div>
    <div class="stat-sub">
      <?= count($pending_list) > 0 ? count($pending_list) . ' menunggu verifikasi' : 'Semua terpantau' ?>
    </div>
  </div>
</div>

<!-- ===== TAGIHAN BELUM BAYAR ===== -->
<?php if (!empty($belum_list)): ?>
<div class="card mb-3" style="border-color:#fecaca;">
  <div class="card-header" style="background:#fff5f5;">
    <span class="card-title">⏳ Tagihan Belum Dibayar</span>
    <span class="badge badge-belum"><?= count($belum_list) ?> tagihan</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <?php if (isAdmin()): ?><th>Nama</th><?php endif; ?>
          <th>Kamar</th><th>Periode</th><th>Jumlah Tagihan</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($belum_list as $t): ?>
      <tr>
        <?php if (isAdmin()): ?>
        <td style="color:#1a1a1a;"><?= htmlspecialchars($t['nama']) ?></td>
        <?php endif; ?>
        <td><strong style="color:#0F6E56;"><?= $t['no_kamar'] ?></strong></td>
        <td style="color:#475569;"><?= $t['bulan'] ?></td>
        <td style="font-weight:700;color:var(--coral);">Rp <?= number_format($t['jumlah'], 0, ',', '.') ?></td>
        <td><span class="badge badge-belum">Belum Bayar</span></td>
        <td>
          <button onclick="openPayModal(<?= $t['id'] ?>, '<?= htmlspecialchars($t['nama']) ?>', '<?= $t['bulan'] ?>', <?= $t['jumlah'] ?>)"
            class="btn btn-primary btn-sm">
            💳 Bayar
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php elseif (empty($belum_list) && !empty($my_trans)): ?>
<div class="alert alert-success mb-3" style="display:flex;align-items:center;gap:10px;">
  <span style="font-size:1.2rem;">🎉</span>
  <span>
    <?php if (isAdmin()): ?>
      Tidak ada tagihan yang belum dibayar saat ini.
    <?php else: ?>
      Semua tagihan kamu sudah lunas. Terima kasih! 🙏
    <?php endif; ?>
  </span>
</div>
<?php endif; ?>

<!-- ===== MENUNGGU VALIDASI (PENDING) ===== -->
<?php if (!empty($pending_list)): ?>
<div class="card mb-3" style="border-color:#fef08a;">
  <div class="card-header" style="background:#fef08a;">
    <span class="card-title">⏳ Menunggu Validasi Admin</span>
    <span class="badge badge-pending"><?= count($pending_list) ?> tagihan</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <?php if (isAdmin()): ?><th>Nama</th><?php endif; ?>
          <th>Kamar</th><th>Periode</th><th>Jumlah</th>
          <th>Metode</th><th>Tgl Upload</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach (array_reverse($pending_list) as $t): ?>
      <tr>
        <?php if (isAdmin()): ?>
        <td style="color:#1a1a1a;"><?= htmlspecialchars($t['nama']) ?></td>
        <?php endif; ?>
        <td><strong style="color:#0F6E56;"><?= $t['no_kamar'] ?></strong></td>
        <td style="color:#475569;"><?= $t['bulan'] ?></td>
        <td style="font-weight:700;color:var(--amber);">Rp <?= number_format($t['jumlah'], 0, ',', '.') ?></td>
        <td class="text-sm text-muted"><?= $t['metode'] ?></td>
        <td class="text-sm text-muted"><?= $t['tanggal'] ?></td>
        <td><span class="badge badge-pending">Pending</span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="padding:.85rem 1.4rem;background:#fef08a;font-size:12px;color:#854d0e;border-top:1px solid #f59e0b;border-radius:0 0 var(--radius-lg) var(--radius-lg);">
    💡 Admin sedang memverifikasi bukti pembayaran. Proses validasi membutuhkan waktu 1×24 jam.
  </div>
</div>
<?php endif; ?>

<!-- ===== RIWAYAT LUNAS ===== -->
<div class="card">
  <div class="card-header">
    <span class="card-title">✅ Riwayat Pembayaran Lunas</span>
    <?php if (!empty($lunas_list)): ?>
    <span style="font-size:12px;color:var(--text-muted);">
      Total: <strong style="color:var(--teal);">Rp <?= number_format($total_masuk, 0, ',', '.') ?></strong>
    </span>
    <?php endif; ?>
  </div>

  <?php if (empty($lunas_list)): ?>
  <div class="empty-state">
    <span class="empty-icon">📭</span>
    <h4>Belum ada transaksi lunas</h4>
    <p>
      <?php if (isAdmin()): ?>
        Belum ada pembayaran yang terkonfirmasi.
      <?php else: ?>
        Riwayat pembayaran kamu akan muncul di sini.
      <?php endif; ?>
    </p>
  </div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <?php if (isAdmin()): ?><th>Nama</th><?php endif; ?>
          <th>Kamar</th><th>Periode</th><th>Jumlah</th>
          <th>Metode</th><th>Tgl Bayar</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach (array_reverse($lunas_list) as $t): ?>
      <tr>
        <?php if (isAdmin()): ?>
        <td style="color:#1a1a1a;"><?= htmlspecialchars($t['nama']) ?></td>
        <?php endif; ?>
        <td><strong style="color:#0F6E56;"><?= $t['no_kamar'] ?></strong></td>
        <td style="color:#475569;"><?= $t['bulan'] ?></td>
        <td style="font-weight:700;color:var(--teal);">Rp <?= number_format($t['jumlah'], 0, ',', '.') ?></td>
        <td class="text-sm text-muted"><?= $t['metode'] ?></td>
        <td class="text-sm text-muted"><?= $t['tanggal'] ?></td>
        <td><span class="badge badge-lunas">✓ Lunas</span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if (empty($my_trans) && !$booked): ?>
<div class="card mt-3" style="background:var(--teal-xlight);border-color:#9FE1CB;">
  <div class="card-body" style="text-align:center;padding:2rem;">
    <div style="font-size:2rem;margin-bottom:.75rem;">🛏️</div>
    <div style="font-weight:700;font-size:14px;color:#1a1a1a;margin-bottom:4px;">Belum ada transaksi</div>
    <div style="font-size:12px;color:var(--text-muted);margin-bottom:1.25rem;">
      Pesan kamar terlebih dahulu untuk mulai sewa.
    </div>
    <a href="index.php?page=kamar" class="btn btn-primary" style="display:inline-block;">
      🛏️ Lihat Katalog Kamar
    </a>
  </div>
</div>
<?php endif; ?>

<div id="payModal" class="modal-overlay">
  <div class="modal-box" style="text-align:left;max-width:400px;">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="bayar">
      <input type="hidden" name="id" id="pay-id">
      <div style="font-weight:700;font-size:15px;margin-bottom:.25rem;color:#1a1a1a;">💳 Konfirmasi Pembayaran</div>
      <div style="font-size:12px;color:var(--text-muted);margin-bottom:1.25rem;">Upload bukti transfer dan pilih metode.</div>
      <div style="background:var(--bg);border-radius:10px;padding:1rem;margin-bottom:1.25rem;">
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);">
          <span class="text-sm text-muted">Nama</span>
          <strong id="pay-nama" style="font-size:13px;color:#1a1a1a;"></strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);">
          <span class="text-sm text-muted">Periode</span>
          <strong id="pay-bulan" style="font-size:13px;color:#1a1a1a;"></strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;">
          <span class="text-sm text-muted">Total Tagihan</span>
          <strong id="pay-jumlah" style="font-size:16px;color:var(--teal);"></strong>
        </div>
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
      <div class="form-group">
        <label>Upload Bukti Transfer *</label>
        <div class="file-drop">
          <input type="file" name="bukti" accept="image/*" onchange="handleFileUpload(this,'fileLabelPay')" required>
          <span class="file-drop-icon">📷</span>
          <div class="file-drop-text" id="fileLabelPay">
            <strong>Klik untuk upload bukti</strong><br>JPG, PNG, WEBP — maks 5MB
          </div>
        </div>
      </div>
      <div style="background:var(--teal-light);border-radius:8px;padding:10px 12px;font-size:11px;color:var(--teal-dark);margin-bottom:1rem;line-height:1.6;">
        <strong>Rekening BRI:</strong> 1234-5678-9012-3456<br>
        a.n. Lukyta Surawijaya<br>
        <span style="opacity:.8;">Konfirmasi via WhatsApp: 0812-3456-7890</span>
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closePayModal()" class="btn btn-outline" style="flex:1;">Batal</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Kirim Bukti</button>
      </div>
    </form>
  </div>
</div>