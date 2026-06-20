<?php
// ============================================================
//  VIEW: Kelola Kamar (Admin)
//  Status kamar dikelola otomatis oleh sistem, tidak bisa diubah manual
// ============================================================

$kamar_data = getKamarData();
$filter     = $_GET['filter'] ?? 'semua';
$edit_id    = (int)($_GET['edit'] ?? 0);
$edit_kamar = null;
if ($edit_id) {
    foreach ($kamar_data as $k) {
        if ($k['id'] == $edit_id) { $edit_kamar = $k; break; }
    }
}

$filtered = $kamar_data;
if ($filter === 'tersedia') $filtered = array_filter($kamar_data, fn($k) => $k['status']==='tersedia');
elseif ($filter === 'terisi') $filtered = array_filter($kamar_data, fn($k) => $k['status']==='terisi');

$tersedia = count(array_filter($kamar_data, fn($k) => $k['status']==='tersedia'));
$terisi   = count($kamar_data) - $tersedia;
?>

<!-- Stats mini -->
<div class="admin-stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.25rem;">
  <div class="admin-stat-card"><div class="admin-stat-icon">🏠</div><div class="admin-stat-val" style="color:#1e293b;"><?= count($kamar_data) ?></div><div class="admin-stat-lbl" style="color:#475569;">Total Kamar</div></div>
  <div class="admin-stat-card"><div class="admin-stat-icon">✅</div><div class="admin-stat-val" style="color:#0F6E56;"><?= $tersedia ?></div><div class="admin-stat-lbl" style="color:#475569;">Tersedia</div></div>
  <div class="admin-stat-card"><div class="admin-stat-icon">🔴</div><div class="admin-stat-val" style="color:#b91c1c;"><?= $terisi ?></div><div class="admin-stat-lbl" style="color:#475569;">Terisi</div></div>
</div>

<div class="admin-grid-2" style="align-items:start;">

  <!-- Tabel Kamar -->
  <div class="admin-card" style="grid-column: span 1;">
    <div class="admin-card-header">
      <span class="admin-card-title" style="color:#1e293b;">🏠 Daftar Kamar</span>
      <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <?php foreach(['semua'=>'Semua','tersedia'=>'Tersedia','terisi'=>'Terisi'] as $v=>$l): ?>
        <a href="admin.php?page=kamar&filter=<?= $v ?>"
           class="admin-btn admin-btn-sm <?= $filter===$v?'admin-btn-primary':'admin-btn-outline' ?>"><?= $l ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="color:#475569;">Kamar</th>
            <th style="color:#475569;">Tipe</th>
            <th style="color:#475569;">Harga</th>
            <th style="color:#475569;">Fasilitas</th>
            <th style="color:#475569;">Status</th>
            <th style="color:#475569;">Foto</th>
            <th style="color:#475569;">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($filtered as $k): ?>
        <tr>
          <td style="font-weight:700;color:#1e293b;"><?= $k['nama'] ?></td>
          <td><span class="admin-badge <?= strtolower($k['tipe'])==='premium'?'admin-badge-premium':'admin-badge-standard' ?>"><?= $k['tipe'] ?></span></td>
          <td style="color:#0F6E56;font-weight:600;">Rp <?= number_format($k['harga'],0,',','.') ?></td>
          <td style="font-size:11px;color:#475569;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= $k['fasilitas'] ?>"><?= $k['fasilitas'] ?></td>
          <td>
            <?php if ($k['status'] === 'terisi'): ?>
              <span class="admin-badge admin-badge-terisi" style="background:#fee2e2;color:#991b1b;">Terisi</span>
            <?php else: ?>
              <span class="admin-badge admin-badge-tersedia" style="background:#dcfce7;color:#166534;">Tersedia</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($k['foto'])): ?>
              <a href="uploads/<?= $k['foto'] ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm" title="Lihat Foto">📷</a>
            <?php else: ?>
              <span style="color:#94a3b8;font-size:10px;">-</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:5px;">
              <a href="admin.php?page=kamar&edit=<?= $k['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">✏️</a>
              <form method="post" style="display:inline;" onsubmit="return confirm('Hapus kamar <?= $k['nama'] ?>?')">
                <input type="hidden" name="action" value="hapus_kamar">
                <input type="hidden" name="id" value="<?= $k['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Form Tambah / Edit Kamar -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title" style="color:#1e293b;"><?= $edit_kamar ? '✏️ Edit Kamar' : '➕ Tambah Kamar' ?></span>
      <?php if($edit_kamar): ?>
      <a href="admin.php?page=kamar" class="admin-btn admin-btn-outline admin-btn-sm">← Batal</a>
      <?php endif; ?>
    </div>
    <div class="admin-card-body">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_kamar">
        <input type="hidden" name="edit_id" value="<?= $edit_kamar['id'] ?? 0 ?>">

        <div class="admin-form-row">
          <div class="admin-form-group">
            <label style="color:#1e293b;">Nama Kamar *</label>
            <input type="text" name="nama" placeholder="Contoh: A1" value="<?= $edit_kamar['nama'] ?? '' ?>" required>
          </div>
          <div class="admin-form-group">
            <label style="color:#1e293b;">Tipe Kamar *</label>
            <select name="tipe" required>
              <option value="Standard" <?= ($edit_kamar['tipe'] ?? '')==='Standard'?'selected':'' ?>>Standard</option>
              <option value="Premium"  <?= ($edit_kamar['tipe'] ?? '')==='Premium'?'selected':'' ?>>Premium (AC)</option>
            </select>
          </div>
        </div>

        <div class="admin-form-row">
          <div class="admin-form-group">
            <label style="color:#1e293b;">Harga / Bulan (Rp) *</label>
            <input type="number" name="harga" placeholder="450000" value="<?= $edit_kamar['harga'] ?? '' ?>" required>
          </div>
          <div class="admin-form-group">
            <label style="color:#1e293b;">Status</label>
            <div style="padding:8px 0;font-size:12px;color:#475569;">
              <?php if (($edit_kamar['status'] ?? 'tersedia') === 'terisi'): ?>
                <span class="admin-badge admin-badge-terisi" style="background:#fee2e2;color:#991b1b;">Terisi</span>
                <span style="font-size:10px;color:#94a3b8;margin-left:8px;">(Otomatis dikelola sistem)</span>
              <?php else: ?>
                <span class="admin-badge admin-badge-tersedia" style="background:#dcfce7;color:#166534;">Tersedia</span>
                <span style="font-size:10px;color:#94a3b8;margin-left:8px;">(Otomatis dikelola sistem)</span>
              <?php endif; ?>
              <input type="hidden" name="status" value="<?= $edit_kamar['status'] ?? 'tersedia' ?>">
            </div>
          </div>
        </div>

        <div class="admin-form-group">
          <label style="color:#1e293b;">Fasilitas <span style="text-transform:none;letter-spacing:0;font-weight:400;color:#94a3b8;">(pisahkan dengan koma)</span></label>
          <input type="text" name="fasilitas" placeholder="WiFi, Kamar Mandi Dalam, Lemari, Meja Belajar"
                 value="<?= $edit_kamar['fasilitas'] ?? '' ?>">
        </div>

        <!-- ===== FOTO KAMAR ===== -->
        <div class="admin-form-group">
          <label style="color:#1e293b;">Foto Kamar <span style="text-transform:none;letter-spacing:0;font-weight:400;color:#94a3b8;">(opsional, max 5MB)</span></label>
          <?php if ($edit_kamar && !empty($edit_kamar['foto'])): ?>
            <div style="margin-bottom:8px;display:flex;align-items:center;gap:10px;">
              <a href="uploads/<?= $edit_kamar['foto'] ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm">📷 Lihat Foto Saat Ini</a>
              <span style="font-size:11px;color:#94a3b8;">Upload baru untuk mengganti</span>
            </div>
          <?php endif; ?>
          <div class="file-drop" style="padding:1rem;">
            <input type="file" name="foto" accept="image/*" onchange="handleFileUpload(this,'fileLabelKamar')">
            <span class="file-drop-icon">🖼️</span>
            <div class="file-drop-text" id="fileLabelKamar">
              <strong>Klik untuk upload foto</strong><br>JPG, PNG, WEBP — maks 5MB
            </div>
          </div>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;">
          <?= $edit_kamar ? '💾 Simpan Perubahan' : '➕ Tambah Kamar' ?>
        </button>
        <div style="margin-top:.5rem;font-size:10px;color:#94a3b8;text-align:center;">
          ⚡ Status kamar akan diatur otomatis saat penghuni ditambahkan atau dihapus.
        </div>
      </form>
    </div>
  </div>

</div>