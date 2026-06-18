<?php
// ============================================================
//  VIEW: Data Penghuni (Admin)
// ============================================================

// Ambil data penghuni
$db = getDB();
if ($db) {
    $result   = $db->query("SELECT * FROM tb_users WHERE role='penghuni' ORDER BY no_kamar ASC");
    $penghuni = [];
    while ($row = $result->fetch_assoc()) $penghuni[] = $row;
} else {
    $penghuni = array_filter(getDemoData('users'), fn($u) => $u['role'] === 'penghuni');
}

$kamar_data = getKamarData();
$kamar_names = array_column($kamar_data, 'nama');
?>

<div class="admin-grid-2" style="align-items:start;">

  <!-- Tabel Penghuni -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">👩 Data Penghuni (<?= count($penghuni) ?>)</span>
    </div>
    <?php if(empty($penghuni)): ?>
    <div class="admin-empty"><span class="admin-empty-icon">👤</span>Belum ada data penghuni</div>
    <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr><th style="color:#475569;">Nama</th><th style="color:#475569;">Kamar</th><th style="color:#475569;">Kontak</th><th style="color:#475569;">Pekerjaan</th><th style="color:#475569;">Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach($penghuni as $p): ?>
        <tr>
          <td>
            <div style="font-weight:700;color:#1a1a1a;"><?= htmlspecialchars($p['nama']) ?></div>
            <div style="font-size:10px;color:#94a3b8;"><?= $p['email'] ?></div>
          </td>
          <td style="font-weight:700;color:#0F6E56;"><?= $p['no_kamar'] ?></td>
          <td style="font-size:11px;color:#475569;"><?= $p['no_hp'] ?? '-' ?></td>
          <td>
            <span class="admin-badge admin-badge-penghuni" style="font-size:9px;"><?= $p['pekerjaan'] ?? '-' ?></span>
          </td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
              <!-- Reset password -->
              <form method="post" style="display:inline;" onsubmit="return confirm('Reset password <?= htmlspecialchars($p['nama']) ?>?')">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-warning admin-btn-sm" title="Reset Password">🔑</button>
              </form>
              <!-- Hapus -->
              <form method="post" style="display:inline;" onsubmit="return confirm('Hapus data <?= htmlspecialchars($p['nama']) ?>?')">
                <input type="hidden" name="action" value="hapus_penghuni">
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

  <!-- Form Tambah Penghuni -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">➕ Tambah Penghuni Baru</span>
    </div>
    <div class="admin-card-body">
      <form method="post">
        <input type="hidden" name="action" value="tambah_penghuni">
        <div class="admin-form-row">
          <div class="admin-form-group">
            <label style="color:#1a1a1a;">Nama Lengkap *</label>
            <input type="text" name="nama" placeholder="Nama penghuni" required>
          </div>
          <div class="admin-form-group">
            <label style="color:#1a1a1a;">No. Kamar *</label>
            <select name="no_kamar" required>
              <option value="">— Pilih kamar —</option>
              <?php foreach($kamar_data as $k): ?>
                <?php if ($k['status'] === 'tersedia'): ?>
                  <option value="<?= preg_replace('/Kamar /', '', $k['nama']) ?>">
                    <?= $k['nama'] ?> (Tersedia)
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="admin-form-group">
          <label style="color:#1a1a1a;">Email *</label>
          <input type="email" name="email" placeholder="email@domain.com" required>
        </div>
        <div class="admin-form-row">
          <div class="admin-form-group">
            <label style="color:#1a1a1a;">No. WhatsApp</label>
            <input type="text" name="no_hp" placeholder="08xxxxxxxxxx">
          </div>
          <div class="admin-form-group">
            <label style="color:#1a1a1a;">Pekerjaan / Status</label>
            <select name="pekerjaan">
              <option>Mahasiswi</option>
              <option>Karyawati</option>
              <option>Wiraswasta</option>
              <option>Lainnya</option>
            </select>
          </div>
        </div>
        <div class="admin-form-group">
          <label style="color:#1a1a1a;">Alamat Asal</label>
          <input type="text" name="alamat" placeholder="Kota asal penghuni">
        </div>
        <div class="admin-form-group">
          <label style="color:#1a1a1a;">Password Awal</label>
          <input type="text" name="password" placeholder="Password awal penghuni">
        </div>
        <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;">➕ Tambah Penghuni</button>
        <div style="margin-top:.5rem;font-size:10px;color:#94a3b8;text-align:center;">Password dapat direset kapan saja lewat tabel di kiri</div>
      </form>
    </div>
  </div>

</div>