<?php
/**
 * ================================================================
 *  VIEW: MANAJEMEN BOOKING (ADMIN)
 *  - Menampilkan daftar booking dari tamu.
 *  - Statistik: Total, Menunggu, Disetujui, Ditolak.
 *  - Fitur: Konfirmasi (setujui), Tolak, Hapus, dan Hubungi via WhatsApp.
 *  - Saat booking disetujui, sistem otomatis membuat akun penghuni
 *    dan mencatat transaksi pertama (via admin/actions/booking.php).
 * ================================================================
 */

// Ambil data booking dari database/session
$db = getDB();
if ($db) {
    $result = $db->query("SELECT * FROM tb_booking ORDER BY id DESC");
    $booking_list = [];
    while ($row = $result->fetch_assoc()) $booking_list[] = $row;
} else {
    $booking_list = $_SESSION['demo_booking'] ?? [];
}

// ---- STATISTIK ----
$total_menunggu = count(array_filter($booking_list ?? [], fn($b) => $b['status'] === 'menunggu'));
$total_disetujui = count(array_filter($booking_list ?? [], fn($b) => $b['status'] === 'disetujui'));
$total_ditolak = count(array_filter($booking_list ?? [], fn($b) => $b['status'] === 'ditolak'));

// ---- FILTER ----
$filter_status = $_GET['status'] ?? 'semua';
if ($filter_status !== 'semua') {
    $booking_list = array_filter($booking_list, fn($b) => $b['status'] === $filter_status);
}
?>

<!-- ===== STATISTIK CARD ===== -->
<div class="admin-stats-grid" style="grid-template-columns:repeat(4,1fr);">
  <div class="admin-stat-card">
    <div class="admin-stat-icon">📋</div>
    <div class="admin-stat-val" style="color:#1e293b;"><?= count($booking_list) ?></div>
    <div class="admin-stat-lbl">Total Booking</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">⏳</div>
    <div class="admin-stat-val" style="color:#d97706;"><?= $total_menunggu ?></div>
    <div class="admin-stat-lbl">Menunggu</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">✅</div>
    <div class="admin-stat-val" style="color:#0F6E56;"><?= $total_disetujui ?></div>
    <div class="admin-stat-lbl">Disetujui</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">❌</div>
    <div class="admin-stat-val" style="color:#dc2626;"><?= $total_ditolak ?></div>
    <div class="admin-stat-lbl">Ditolak</div>
  </div>
</div>

<!-- ===== TABEL DAFTAR BOOKING ===== -->
<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title">📋 Daftar Booking</span>
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
      <?php foreach(['semua'=>'Semua','menunggu'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak'] as $v=>$l): ?>
      <a href="admin.php?page=booking&status=<?= $v ?>"
         class="admin-btn admin-btn-sm <?= $filter_status===$v?'admin-btn-primary':'admin-btn-outline' ?>"><?= $l ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if(empty($booking_list)): ?>
  <div class="admin-empty">
    <span class="admin-empty-icon">📭</span>
    Belum ada booking
  </div>
  <?php else: ?>
  <div class="admin-table-wrap">
    <table class="admin-table admin-table-transaksi">
      <thead>
        <tr>
          <th style="color:#475569;width:30px;">#</th>
          <th style="color:#475569;">Nama</th>
          <th style="color:#475569;">No. HP</th>
          <th style="color:#475569;">Kamar</th>
          <th style="color:#475569;">Periode</th>
          <th style="color:#475569;">Metode</th>
          <th style="color:#475569;">Status</th>
          <th style="color:#475569;">Bukti</th>
          <th style="color:#475569;min-width:120px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $no = 1;
      foreach($booking_list as $b):
        $bg_status = $b['status'] === 'menunggu' ? '#fef3c7' : ($b['status'] === 'disetujui' ? '#dcfce7' : '#fee2e2');
        $color_status = $b['status'] === 'menunggu' ? '#92400e' : ($b['status'] === 'disetujui' ? '#166534' : '#991b1b');
        $label_status = $b['status'] === 'menunggu' ? 'Menunggu' : ($b['status'] === 'disetujui' ? '✅ Disetujui' : '❌ Ditolak');
      ?>
      <tr>
        <td style="color:#94a3b8;font-size:11px;"><?= $no++ ?></td>
        <td style="font-weight:700;color:#1e293b;"><?= htmlspecialchars($b['nama']) ?></td>
        <td style="color:#475569;font-size:12px;"><?= htmlspecialchars($b['no_hp']) ?></td>
        <td style="font-weight:700;color:#0F6E56;"><?= htmlspecialchars($b['kamar_nama']) ?></td>
        <td style="color:#475569;font-size:11px;"><?= htmlspecialchars($b['periode']) ?></td>
        <td style="color:#475569;font-size:11px;"><?= htmlspecialchars($b['metode']) ?></td>
        <td>
          <span style="background:<?= $bg_status ?>;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600;color:<?= $color_status ?>;">
            <?= $label_status ?>
          </span>
        </td>
        <td>
          <?php if ($b['bukti']): ?>
            <a href="uploads/pembayaran/<?= $b['bukti'] ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm">📷</a>
          <?php else: ?>
            <span style="color:#94a3b8;">-</span>
          <?php endif; ?>
        </td>
        <td>
          <div style="display:flex;gap:4px;flex-wrap:wrap;">
            <?php if ($b['status'] === 'menunggu'): ?>
              <!-- Form Konfirmasi / Setujui -->
              <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="konfirmasi_booking">
                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-success admin-btn-sm" title="Setujui">✅</button>
              </form>
              <!-- Form Tolak -->
              <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="tolak_booking">
                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" title="Tolak">❌</button>
              </form>
            <?php endif; ?>
            <!-- Hubungi via WhatsApp -->
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $b['no_hp']) ?>?text=Halo%20<?= urlencode($b['nama']) ?>%2C%20saya%20dari%20Kost%20Putri%20Capoeng.%20Booking%20kamar%20<?= urlencode($b['kamar_nama']) ?>%20Anda%20sedang%20kami%20proses."
               target="_blank" class="admin-btn admin-btn-success admin-btn-sm" title="Hubungi WA">💬</a>
            <!-- Hapus Booking -->
            <form method="post" style="display:inline;" onsubmit="return confirm('Hapus booking ini?')">
              <input type="hidden" name="action" value="hapus_booking">
              <input type="hidden" name="id" value="<?= $b['id'] ?>">
              <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">🗑️</button>
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