<?php
/**
 * ================================================================
 *  VIEW: MANAJEMEN PESAN (ADMIN)
 *  - Menampilkan daftar pesan masuk dari tamu/penghuni.
 *  - Statistik: Total, Baru, Dibaca, Dibalas.
 *  - Fitur: Update status (dropdown langsung di tabel), Detail (modal), Balas via WhatsApp, Hapus.
 *  - Detail modal menampilkan isi pesan dan link untuk balas langsung ke WhatsApp.
 * ================================================================
 */

// Ambil data pesan dari database/session
$db = getDB();
if ($db) {
    $result = $db->query("SELECT * FROM tb_pesan ORDER BY id DESC");
    $pesan_list = [];
    while ($row = $result->fetch_assoc()) $pesan_list[] = $row;
} else {
    $pesan_list = $_SESSION['demo_pesan'] ?? [];
}

// ---- STATISTIK ----
$total_all  = count($pesan_list);
$total_baru = count(array_filter($pesan_list ?? [], fn($p) => $p['status'] === 'baru'));
$total_dibaca = count(array_filter($pesan_list ?? [], fn($p) => $p['status'] === 'dibaca'));
$total_dibalas = count(array_filter($pesan_list ?? [], fn($p) => $p['status'] === 'dibalas'));

// ---- FILTER ----
$filter_status = $_GET['status'] ?? 'semua';
if ($filter_status !== 'semua') {
    $pesan_list = array_filter($pesan_list, fn($p) => $p['status'] === $filter_status);
}
?>

<!-- ===== STATISTIK CARD ===== -->
<div class="admin-stats-grid" style="grid-template-columns:repeat(4,1fr);">
  <div class="admin-stat-card">
    <div class="admin-stat-icon">📩</div>
    <div class="admin-stat-val" style="color:#1e293b;"><?= count($pesan_list) ?></div>
    <div class="admin-stat-lbl">Total Pesan</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">🔵</div>
    <div class="admin-stat-val" style="color:#dc2626;"><?= $total_baru ?></div>
    <div class="admin-stat-lbl">Baru</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">📖</div>
    <div class="admin-stat-val" style="color:#0F6E56;"><?= $total_dibaca ?></div>
    <div class="admin-stat-lbl">Dibaca</div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-icon">✅</div>
    <div class="admin-stat-val" style="color:#10b981;"><?= $total_dibalas ?></div>
    <div class="admin-stat-lbl">Dibalas</div>
  </div>
</div>

<!-- ===== TABEL DAFTAR PESAN ===== -->
<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title">📩 Daftar Pesan Masuk</span>
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
      <?php foreach(['semua'=>'Semua','baru'=>'Baru','dibaca'=>'Dibaca','dibalas'=>'Dibalas'] as $v=>$l): ?>
      <a href="admin.php?page=pesan&status=<?= $v ?>"
         class="admin-btn admin-btn-sm <?= $filter_status===$v?'admin-btn-primary':'admin-btn-outline' ?>"><?= $l ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if(empty($pesan_list)): ?>
  <div class="admin-empty">
    <span class="admin-empty-icon">📭</span>
    Belum ada pesan masuk
  </div>
  <?php else: ?>
  <div class="admin-table-wrap">
    <table class="admin-table admin-table-transaksi">
      <thead>
        <tr>
          <th style="color:#475569;width:30px;">#</th>
          <th style="color:#475569;">Nama</th>
          <th style="color:#475569;">No. HP</th>
          <th style="color:#475569;">Topik</th>
          <th style="color:#475569;">Pesan</th>
          <th style="color:#475569;">Status</th>
          <th style="color:#475569;">Tgl Kirim</th>
          <th style="color:#475569;min-width:120px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php 
      $no = 1;
      foreach($pesan_list as $p): 
        $bg_status = $p['status'] === 'baru' ? '#fee2e2' : ($p['status'] === 'dibaca' ? '#fef3c7' : '#dcfce7');
        $color_status = $p['status'] === 'baru' ? '#991b1b' : ($p['status'] === 'dibaca' ? '#92400e' : '#166534');
      ?>
      <tr>
        <td style="color:#94a3b8;font-size:11px;"><?= $no++ ?></td>
        <td style="font-weight:700;color:#1e293b;"><?= htmlspecialchars($p['nama']) ?></td>
        <td style="color:#475569;font-size:12px;"><?= htmlspecialchars($p['no_hp']) ?></td>
        <td><span style="background:#e5e7eb;padding:2px 8px;border-radius:4px;font-size:10px;color:#1e293b;"><?= htmlspecialchars($p['topik']) ?></span></td>
        <td style="color:#475569;font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($p['pesan']) ?>">
          <?= htmlspecialchars(mb_strimwidth($p['pesan'], 0, 50, '...')) ?>
        </td>
        <td>
          <!-- Form Update Status langsung di tabel -->
          <form method="post" style="display:inline;">
            <input type="hidden" name="action" value="update_status_pesan">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <select name="status" onchange="this.form.submit()"
              style="padding:2px 6px;font-size:11px;border-radius:6px;background:<?= $bg_status ?>;border:1px solid #cbd5e1;color:<?= $color_status ?>;cursor:pointer;font-weight:600;">
              <option value="baru"    <?= $p['status']==='baru'?'selected':'' ?>>Baru</option>
              <option value="dibaca"  <?= $p['status']==='dibaca'?'selected':'' ?>>Dibaca</option>
              <option value="dibalas" <?= $p['status']==='dibalas'?'selected':'' ?>>Dibalas</option>
            </select>
          </form>
        </td>
        <td style="font-size:11px;color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
        <td>
          <div style="display:flex;gap:4px;flex-wrap:wrap;">
            <!-- Balas via WhatsApp -->
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $p['no_hp']) ?>?text=Halo%20<?= urlencode($p['nama']) ?>%2C%20saya%20dari%20Kost%20Putri%20Capoeng.%20Pesan%20Anda%3A%20<?= urlencode($p['pesan']) ?>"
               target="_blank" class="admin-btn admin-btn-success admin-btn-sm" title="Balas via WhatsApp">
              💬
            </a>
            <!-- Detail Modal -->
            <button onclick="openDetailPesan(<?= $p['id'] ?>, '<?= addslashes($p['nama']) ?>', '<?= addslashes($p['no_hp']) ?>', '<?= addslashes($p['topik']) ?>', '<?= addslashes($p['pesan']) ?>', '<?= $p['status'] ?>')"
              class="admin-btn admin-btn-outline admin-btn-sm" title="Detail">👁️</button>
            <!-- Hapus -->
            <form method="post" style="display:inline;" onsubmit="return confirm('Hapus pesan ini?')">
              <input type="hidden" name="action" value="hapus_pesan">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
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

<!-- ===== MODAL DETAIL PESAN ===== -->
<div id="detailPesanModal" class="modal-overlay">
  <div class="modal-box" style="max-width:500px;text-align:left;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <h3 style="font-size:16px;color:#1e293b;margin:0;">📩 Detail Pesan</h3>
      <button type="button" onclick="closeDetailPesan()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div style="background:#f8fafc;border-radius:10px;padding:1rem;margin-bottom:1rem;">
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e5e7eb;">
        <span style="color:#475569;font-weight:600;">Nama</span>
        <span id="detail-nama" style="color:#1e293b;font-weight:600;"></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e5e7eb;">
        <span style="color:#475569;font-weight:600;">No. HP</span>
        <span id="detail-nohp" style="color:#0F6E56;font-weight:600;"></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e5e7eb;">
        <span style="color:#475569;font-weight:600;">Topik</span>
        <span id="detail-topik" style="color:#1e293b;font-weight:600;"></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e5e7eb;">
        <span style="color:#475569;font-weight:600;">Status</span>
        <span id="detail-status" style="font-weight:600;"></span>
      </div>
    </div>
    <div style="background:#f1f5f9;border-radius:10px;padding:1rem;">
      <div style="font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Isi Pesan</div>
      <div id="detail-pesan" style="color:#1e293b;line-height:1.7;font-size:13px;"></div>
    </div>
    <div style="margin-top:1.25rem;display:flex;gap:8px;">
      <button onclick="closeDetailPesan()" class="btn btn-outline" style="flex:1;">Tutup</button>
      <a id="detail-wa-link" href="#" target="_blank" class="btn btn-primary" style="flex:1;text-decoration:none;text-align:center;">💬 Balas WA</a>
    </div>
  </div>
</div>

<!-- ===== JAVASCRIPT UNTUK MODAL DETAIL ===== -->
<script>
function openDetailPesan(id, nama, nohp, topik, pesan, status) {
  document.getElementById('detail-nama').textContent = nama;
  document.getElementById('detail-nohp').textContent = nohp;
  document.getElementById('detail-topik').textContent = topik;
  document.getElementById('detail-pesan').textContent = pesan;
  
  const statusMap = {'baru':'🔵 Baru', 'dibaca':'📖 Dibaca', 'dibalas':'✅ Dibalas'};
  const colorMap = {'baru':'#dc2626', 'dibaca':'#92400e', 'dibalas':'#166534'};
  document.getElementById('detail-status').textContent = statusMap[status] || status;
  document.getElementById('detail-status').style.color = colorMap[status] || '#1e293b';
  
  const waLink = document.getElementById('detail-wa-link');
  const hpClean = nohp.replace(/[^0-9]/g, '');
  waLink.href = 'https://wa.me/' + hpClean + '?text=Halo%20' + encodeURIComponent(nama) + '%2C%20saya%20dari%20Kost%20Putri%20Capoeng.%20Pesan%20Anda%3A%20' + encodeURIComponent(pesan);
  
  document.getElementById('detailPesanModal').classList.add('show');
}

function closeDetailPesan() {
  document.getElementById('detailPesanModal').classList.remove('show');
}
</script>