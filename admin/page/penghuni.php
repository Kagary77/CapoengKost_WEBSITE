<?php
// ============================================================
//  VIEW: Data Penghuni (Admin)
//  Status kamar otomatis dikelola sistem
// ============================================================

$db = getDB();
if ($db) {
    $result   = $db->query("SELECT * FROM tb_users WHERE role='penghuni' ORDER BY no_kamar ASC");
    $penghuni = [];
    while ($row = $result->fetch_assoc()) $penghuni[] = $row;
} else {
    $penghuni = array_filter(getDemoData('users'), fn($u) => $u['role'] === 'penghuni');
}

$kamar_data = getKamarData();
?>

<div class="admin-grid-2" style="align-items:start;">

  <!-- Tabel Penghuni -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title" style="color:#1e293b;">👩 Data Penghuni (<?= count($penghuni) ?>)</span>
    </div>
    <?php if(empty($penghuni)): ?>
    <div class="admin-empty"><span class="admin-empty-icon">👤</span>Belum ada data penghuni</div>
    <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table admin-table-penghuni">
        <thead>
          <tr>
            <th style="width:22%;">Nama</th>
            <th style="width:10%;">Kamar</th>
            <th style="width:15%;">Kontak</th>
            <th style="width:12%;">Pekerjaan</th>
            <th style="width:12%;">Status</th>
            <th style="width:10%;">Detail</th>
            <th style="width:19%;">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($penghuni as $p): ?>
        <tr>
          <td>
            <div style="font-weight:600;color:#1a1a1a;font-size:13px;"><?= htmlspecialchars($p['nama']) ?></div>
            <div style="font-size:11px;color:#94a3b8;margin-top:1px;"><?= $p['email'] ?></div>
          </td>
          <td style="font-weight:700;color:#0F6E56;font-size:14px;text-align:center;"><?= $p['no_kamar'] ?></td>
          <td style="font-size:12px;color:#475569;"><?= $p['no_hp'] ?? '-' ?></td>
          <td>
            <span class="admin-badge admin-badge-penghuni" style="font-size:10px;background:#ede9fe;color:#5b21b6;padding:2px 10px;"><?= $p['pekerjaan'] ?? '-' ?></span>
          </td>
          <td style="text-align:center;">
            <?php
            $status_kamar = 'Tersedia';
            $status_color = '#dcfce7';
            $status_text_color = '#166534';
            foreach ($kamar_data as $k) {
                if ($k['nama'] == $p['no_kamar']) {
                    if ($k['status'] === 'terisi') {
                        $status_kamar = 'Terisi';
                        $status_color = '#fee2e2';
                        $status_text_color = '#991b1b';
                    }
                    break;
                }
            }
            ?>
            <span style="display:inline-block;background:<?= $status_color ?>;padding:2px 12px;border-radius:99px;font-size:10px;font-weight:600;color:<?= $status_text_color ?>;">
              <?= $status_kamar ?>
            </span>
          </td>
          <td style="text-align:center;">
            <button onclick="openDetailPenghuni(
              '<?= addslashes($p['nama']) ?>',
              '<?= addslashes($p['email']) ?>',
              '<?= addslashes($p['no_hp'] ?? '-') ?>',
              '<?= addslashes($p['no_kamar']) ?>',
              '<?= addslashes($p['alamat_asal'] ?? '-') ?>',
              '<?= addslashes($p['pekerjaan'] ?? '-') ?>',
              '<?= addslashes($p['foto_ktp'] ?? '') ?>'
            )" class="admin-btn admin-btn-outline admin-btn-sm" title="Lihat Detail" style="padding:4px 8px;font-size:12px;">👁️</button>
          </td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap;justify-content:center;">
              <form method="post" style="display:inline;" onsubmit="return confirm('Reset password <?= htmlspecialchars($p['nama']) ?>?')">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-warning admin-btn-sm" title="Reset Password" style="padding:4px 8px;font-size:12px;">🔑</button>
              </form>
              <form method="post" style="display:inline;" onsubmit="return confirm('Hapus data <?= htmlspecialchars($p['nama']) ?>?')">
                <input type="hidden" name="action" value="hapus_penghuni">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" title="Hapus Penghuni" style="padding:4px 8px;font-size:12px;">🗑️</button>
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

  <!-- Form Tambah Penghuni -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title" style="color:#1e293b;">➕ Tambah Penghuni Baru</span>
    </div>
    <div class="admin-card-body">
      <form method="post">
        <input type="hidden" name="action" value="tambah_penghuni">
        <div class="admin-form-row">
          <div class="admin-form-group">
            <label style="color:#1e293b;">Nama Lengkap *</label>
            <input type="text" name="nama" placeholder="Nama penghuni" required>
          </div>
          <div class="admin-form-group">
            <label style="color:#1e293b;">No. Kamar *</label>
            <select name="no_kamar" required>
              <option value="">— Pilih kamar —</option>
              <?php foreach($kamar_data as $k): ?>
                <?php if ($k['status'] === 'tersedia'): ?>
                  <option value="<?= $k['nama'] ?>">
                    <?= $k['nama'] ?> (Tersedia)
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="admin-form-group">
          <label style="color:#1e293b;">Email *</label>
          <input type="email" name="email" placeholder="email@domain.com" required>
        </div>
        <div class="admin-form-row">
          <div class="admin-form-group">
            <label style="color:#1e293b;">No. WhatsApp</label>
            <input type="text" name="no_hp" placeholder="08xxxxxxxxxx">
          </div>
          <div class="admin-form-group">
            <label style="color:#1e293b;">Pekerjaan / Status</label>
            <select name="pekerjaan">
              <option>Mahasiswi</option>
              <option>Karyawati</option>
              <option>Wiraswasta</option>
              <option>Lainnya</option>
            </select>
          </div>
        </div>
        <div class="admin-form-group">
          <label style="color:#1e293b;">Alamat Asal</label>
          <input type="text" name="alamat" placeholder="Kota asal penghuni">
        </div>
        <div class="admin-form-group">
          <label style="color:#1e293b;">Password Awal</label>
          <input type="text" name="password" placeholder="Password awal penghuni" value="penghuni123">
        </div>
        <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;">➕ Tambah Penghuni</button>
        <div style="margin-top:.5rem;font-size:10px;color:#94a3b8;text-align:center;">
          ⚡ Status kamar akan otomatis berubah menjadi <strong>Terisi</strong> saat penghuni ditambahkan.
        </div>
      </form>
    </div>
  </div>

</div>

<!-- ===== MODAL DETAIL PENGHUNI ===== -->
<div id="detailPenghuniModal" class="modal-overlay">
  <div class="modal-box" style="max-width:450px;text-align:left;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <h3 style="font-size:16px;color:#1e293b;margin:0;">👤 Detail Penghuni</h3>
      <button type="button" onclick="closeDetailPenghuni()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div style="background:#f8fafc;border-radius:10px;padding:1rem;margin-bottom:1rem;">
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e5e7eb;">
        <span style="color:#475569;font-weight:600;">Nama</span>
        <span id="detail-nama" style="color:#1e293b;font-weight:600;"></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e5e7eb;">
        <span style="color:#475569;font-weight:600;">Email</span>
        <span id="detail-email" style="color:#1e293b;font-weight:600;"></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e5e7eb;">
        <span style="color:#475569;font-weight:600;">No. HP</span>
        <span id="detail-nohp" style="color:#0F6E56;font-weight:600;"></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e5e7eb;">
        <span style="color:#475569;font-weight:600;">Kamar</span>
        <span id="detail-kamar" style="color:#0F6E56;font-weight:700;"></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e5e7eb;">
        <span style="color:#475569;font-weight:600;">Alamat Asal</span>
        <span id="detail-alamat" style="color:#1e293b;font-weight:600;"></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e5e7eb;">
        <span style="color:#475569;font-weight:600;">Pekerjaan</span>
        <span id="detail-pekerjaan" style="color:#1e293b;font-weight:600;"></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;">
        <span style="color:#475569;font-weight:600;">Foto KTP</span>
        <span id="detail-fotoktp" style="color:#0F6E56;font-weight:600;"></span>
      </div>
    </div>
    <div style="margin-top:0.5rem;display:flex;gap:8px;">
      <button onclick="closeDetailPenghuni()" class="btn btn-outline" style="flex:1;">Tutup</button>
    </div>
  </div>
</div>

<script>
function openDetailPenghuni(nama, email, nohp, kamar, alamat, pekerjaan, fotoktp) {
  document.getElementById('detail-nama').textContent = nama;
  document.getElementById('detail-email').textContent = email;
  document.getElementById('detail-nohp').textContent = nohp;
  document.getElementById('detail-kamar').textContent = kamar;
  document.getElementById('detail-alamat').textContent = alamat;
  document.getElementById('detail-pekerjaan').textContent = pekerjaan;
  if (fotoktp) {
    document.getElementById('detail-fotoktp').innerHTML = '<a href="uploads/' + fotoktp + '" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm">📷 Lihat KTP</a>';
  } else {
    document.getElementById('detail-fotoktp').textContent = '-';
  }
  document.getElementById('detailPenghuniModal').classList.add('show');
}

function closeDetailPenghuni() {
  document.getElementById('detailPenghuniModal').classList.remove('show');
}
</script>