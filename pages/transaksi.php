<?php
$user_kamar = strtoupper(trim($_SESSION['user_kamar'] ?? ''));
$user_nama  = $_SESSION['user_nama'] ?? '';
$user_id    = $_SESSION['user_id']   ?? 0;

$my_trans    = getMyTransaksi();

$terkirim_list = array_values(array_filter($my_trans, fn($t) => $t['status'] === 'terkirim'));
$pending_list  = array_values(array_filter($my_trans, fn($t) => $t['status'] === 'pending'));
$lunas_list    = array_values(array_filter($my_trans, fn($t) => $t['status'] === 'lunas'));
$gagal_list    = array_values(array_filter($my_trans, fn($t) => $t['status'] === 'gagal'));

$total_terkirim = count($terkirim_list);
$total_pending  = count($pending_list);
$total_lunas    = count($lunas_list);
$total_gagal    = count($gagal_list);
$total_masuk    = array_sum(array_column($lunas_list, 'jumlah'));
$total_belum    = array_sum(array_column($terkirim_list, 'jumlah')); // yang belum dibayar

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
    <div class="stat-sub"><?= $total_lunas ?> transaksi lunas</div>
  </div>
  <div class="trans-card">
    <div class="stat-icon">⏳</div>
    <div class="stat-val" style="font-size:1.25rem;color:var(--coral);">
      Rp <?= number_format($total_belum, 0, ',', '.') ?>
    </div>
    <div class="stat-lbl">Total Belum Dibayar</div>
    <div class="stat-sub"><?= $total_terkirim ?> tagihan terkirim</div>
  </div>
  <div class="trans-card">
    <div class="stat-icon">📋</div>
    <div class="stat-val"><?= count($my_trans) ?></div>
    <div class="stat-lbl">Total Transaksi</div>
    <div class="stat-sub">
      <?= $total_pending > 0 ? $total_pending . ' menunggu validasi' : 'Semua terpantau' ?>
    </div>
  </div>
</div>

<!-- ===== TAGIHAN TERKIRIM (harus diisi penghuni) ===== -->
<?php if (!empty($terkirim_list)): ?>
<div class="card mb-3" style="border-color:#60a5fa;">
  <div class="card-header" style="background:#eff6ff;">
    <span class="card-title">📨 Tagihan Masuk</span>
    <span class="badge badge-terkirim"><?= count($terkirim_list) ?> tagihan</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Periode</th>
          <th>Jumlah Tagihan</th>
          <th>Metode</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($terkirim_list as $t): ?>
      <tr>
        <td><?= $t['bulan'] ?></td>
        <td style="font-weight:700;color:#2563eb;">Rp <?= number_format($t['jumlah'],0,',','.') ?></td>
        <td><?= $t['metode'] ?></td>
        <td>
          <button onclick="openFormBayar(<?= $t['id'] ?>, '<?= addslashes($t['bulan']) ?>', <?= $t['jumlah'] ?>)" 
              class="btn btn-primary btn-sm">
            📤 Kirim Bukti
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="padding:.85rem 1.4rem;background:#eff6ff;font-size:12px;color:#1e40af;border-top:1px solid #93c5fd;border-radius:0 0 var(--radius-lg) var(--radius-lg);">
    💡 Klik "Kirim Bukti" untuk mengisi metode pembayaran dan upload bukti transfer. Setelah dikirim, tagihan akan diverifikasi admin.
  </div>
</div>
<?php elseif (empty($my_trans) && !$booked && (empty($user_kamar) || $user_kamar === '-')): ?>
<!-- Pesan khusus jika belum memiliki kamar -->
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

<!-- ===== MENUNGGU VALIDASI ADMIN (PENDING) ===== -->
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
          <th>Periode</th>
          <th>Jumlah</th>
          <th>Metode</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($pending_list as $t): ?>
      <tr>
        <td><?= $t['bulan'] ?></td>
        <td style="font-weight:700;color:var(--amber);">Rp <?= number_format($t['jumlah'],0,',','.') ?></td>
        <td><?= $t['metode'] ?></td>
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
        <td style="font-weight:700;color:var(--teal);">Rp <?= number_format($t['jumlah'],0,',','.') ?></td>
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

<?php if (!empty($gagal_list)): ?>
<div class="card mt-3" style="border-color:#fecaca;">
  <div class="card-header" style="background:#fee2e2;">
    <span class="card-title">❌ Tagihan Gagal Validasi</span>
    <span class="badge badge-gagal"><?= count($gagal_list) ?></span>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Periode</th><th>Jumlah</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($gagal_list as $t): ?>
      <tr>
        <td><?= $t['bulan'] ?></td>
        <td style="font-weight:700;color:#dc2626;">Rp <?= number_format($t['jumlah'],0,',','.') ?></td>
        <td><span class="badge badge-gagal">Gagal</span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="padding:.85rem 1.4rem;background:#fee2e2;font-size:12px;color:#991b1b;border-top:1px solid #fecaca;border-radius:0 0 var(--radius-lg) var(--radius-lg);">
    💡 Tagihan ini ditolak admin dan otomatis dikirim ulang ke Anda. Silakan cek bagian "Tagihan Masuk" untuk mengisi ulang.
  </div>
</div>
<?php endif; ?>

<!-- ===== MODAL FORM KIRIM TAGIHAN ===== -->
<div id="formBayarModal" class="modal-overlay">
  <div class="modal-box" style="text-align:left;max-width:400px;">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="kirim_tagihan">
      <input type="hidden" name="id" id="form-id">
      <div style="font-weight:700;font-size:15px;margin-bottom:.25rem;color:#1a1a1a;">📤 Kirim Tagihan ke Admin</div>
      <div style="font-size:12px;color:var(--text-muted);margin-bottom:1.25rem;">Isi metode pembayaran dan upload bukti transfer.</div>
      <div style="background:var(--bg);border-radius:10px;padding:1rem;margin-bottom:1.25rem;">
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);">
          <span class="text-sm text-muted">Periode</span>
          <strong id="form-bulan" style="font-size:13px;color:#1a1a1a;"></strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;">
          <span class="text-sm text-muted">Total Tagihan</span>
          <strong id="form-jumlah" style="font-size:16px;color:var(--teal);"></strong>
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
          <input type="file" name="bukti" accept="image/*" onchange="handleFileUpload(this,'fileLabelForm')" required>
          <span class="file-drop-icon">📷</span>
          <div class="file-drop-text" id="fileLabelForm">
            <strong>Klik untuk upload bukti</strong><br>JPG, PNG, WEBP — maks 5MB
          </div>
        </div>
      </div>
      <div style="background:var(--teal-light);border-radius:8px;padding:10px 12px;font-size:11px;color:var(--teal-dark);margin-bottom:1rem;line-height:1.6;">
        <strong>Rekening BRI:</strong> 1234-5678-9012-3456<br>
        a.n. Lukyta Surawijaya
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeFormBayar()" class="btn btn-outline" style="flex:1;">Batal</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Kirim ke Admin</button>
      </div>
    </form>
  </div>
</div>

<script>
function openFormBayar(id, bulan, jumlah) {
    document.getElementById('form-id').value = id;
    document.getElementById('form-bulan').textContent = bulan;
    document.getElementById('form-jumlah').textContent = 'Rp ' + Number(jumlah).toLocaleString('id-ID');
    document.getElementById('formBayarModal').classList.add('show');
}
function closeFormBayar() {
    document.getElementById('formBayarModal').classList.remove('show');
}
</script>