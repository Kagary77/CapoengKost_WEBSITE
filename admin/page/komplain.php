<?php
/**
 * ================================================================
 *  VIEW: MANAJEMEN KOMPLAIN (ADMIN)
 *  - Menampilkan daftar komplain dengan filter status.
 *  - Statistik: Total, Baru, Diproses, Selesai.
 *  - Fitur: Update status (dropdown langsung di tabel), Detail per komplain, Hapus.
 *  - Detail view menampilkan informasi lengkap + deskripsi + foto (jika ada).
 * ================================================================
 */

$komplain_all  = getKomplainAll();

// ---- STATISTIK ----
$cnt_baru     = count(array_filter($komplain_all, fn($k) => $k['status']==='baru'));
$cnt_diproses = count(array_filter($komplain_all, fn($k) => $k['status']==='diproses'));
$cnt_selesai  = count(array_filter($komplain_all, fn($k) => $k['status']==='selesai'));

// ---- FILTER ----
$filter_status = $_GET['status'] ?? 'semua';
$filtered = $komplain_all;
if ($filter_status !== 'semua') {
    $filtered = array_filter($komplain_all, fn($k) => $k['status'] === $filter_status);
}

// ---- DETAIL VIEW (jika parameter 'detail' ada) ----
$detail_id = (int)($_GET['detail'] ?? 0);
$detail = null;
if ($detail_id) {
    foreach ($komplain_all as $k) {
        if ($k['id'] == $detail_id) { $detail = $k; break; }
    }
}

// Mapping badge untuk status
$bdg = ['baru'=>'admin-badge-baru','diproses'=>'admin-badge-diproses','selesai'=>'admin-badge-selesai'];
$lbl = ['baru'=>'Baru','diproses'=>'Diproses','selesai'=>'Selesai'];
?>

<!-- ===== STATISTIK CARD ===== -->
<div class="admin-stats-grid" style="grid-template-columns:repeat(4,1fr);">
  <div class="admin-stat-card"><div class="admin-stat-icon">📋</div><div class="admin-stat-val" style="color:#1e293b;"><?= count($komplain_all) ?></div><div class="admin-stat-lbl" style="color:#475569;">Total</div></div>
  <div class="admin-stat-card"><div class="admin-stat-icon">🔵</div><div class="admin-stat-val" style="color:#1e293b;"><?= $cnt_baru ?></div><div class="admin-stat-lbl" style="color:#475569;">Baru</div></div>
  <div class="admin-stat-card"><div class="admin-stat-icon">🟡</div><div class="admin-stat-val" style="color:#1e293b;"><?= $cnt_diproses ?></div><div class="admin-stat-lbl" style="color:#475569;">Diproses</div></div>
  <div class="admin-stat-card"><div class="admin-stat-icon">🟢</div><div class="admin-stat-val" style="color:#1e293b;"><?= $cnt_selesai ?></div><div class="admin-stat-lbl" style="color:#475569;">Selesai</div></div>
</div>

<!-- ===== DETAIL KOMPLAIN (Jika ada) ===== -->
<?php if ($detail): ?>
<div class="admin-card" style="border-color:#0F6E56;">
  <div class="admin-card-header">
    <span class="admin-card-title" style="color:#1e293b;">🔍 Detail Komplain — <?= $detail['no_tiket'] ?></span>
    <a href="admin.php?page=komplain" class="admin-btn admin-btn-outline admin-btn-sm">✕ Tutup</a>
  </div>
  <div class="admin-card-body">
    <div class="admin-grid-2">
      <!-- Kolom Kiri: Data Pelapor -->
      <div>
        <div style="margin-bottom:.75rem;"><span style="font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;">Pelapor</span><div style="color:#1e293b;font-weight:600;margin-top:2px;"><?= htmlspecialchars($detail['nama']) ?></div></div>
        <div style="margin-bottom:.75rem;"><span style="font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;">No. Kamar</span><div style="color:#0F6E56;font-weight:700;margin-top:2px;font-size:1.1rem;"><?= $detail['no_kamar'] ?></div></div>
        <div style="margin-bottom:.75rem;"><span style="font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;">No. HP</span><div style="color:#1e293b;margin-top:2px;"><?= $detail['no_hp'] ?: '-' ?></div></div>
        <div><span style="font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;">Tanggal</span><div style="color:#1e293b;margin-top:2px;"><?= $detail['created_at'] ?></div></div>
      </div>
      <!-- Kolom Kanan: Kategori, Status, Update Status, Foto -->
      <div>
        <div style="margin-bottom:.75rem;"><span style="font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;">Kategori</span><div style="color:#0F6E56;font-weight:600;margin-top:2px;"><?= $detail['kategori'] ?></div></div>
        <div style="margin-bottom:.75rem;"><span style="font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;">Status</span>
          <div style="margin-top:4px;"><span class="admin-badge <?= $bdg[$detail['status']] ?? '' ?>"><?= $lbl[$detail['status']] ?? $detail['status'] ?></span></div>
        </div>
        <div>
          <span style="font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;">Update Status</span>
          <form method="post" style="margin-top:5px;display:flex;gap:6px;flex-wrap:wrap;">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" value="<?= $detail['id'] ?>">
            <select name="status" style="padding:5px 8px;background:#f1f5f9;border:1px solid #cbd5e1;border-radius:7px;color:#1e293b;font-size:12px;">
              <option value="baru"     <?= $detail['status']==='baru'?'selected':'' ?>>Baru</option>
              <option value="diproses" <?= $detail['status']==='diproses'?'selected':'' ?>>Diproses</option>
              <option value="selesai"  <?= $detail['status']==='selesai'?'selected':'' ?>>Selesai</option>
            </select>
            <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Simpan</button>
          </form>
        </div>
        <?php if ($detail['foto']): ?>
        <div style="margin-top:1rem;">
          <span style="font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;">Foto Dokumentasi</span>
          <div style="margin-top:6px;">
            <a href="uploads/<?= $detail['foto'] ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm">📷 Lihat Foto</a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <!-- Deskripsi Masalah -->
    <div style="margin-top:1rem;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:1rem;">
      <div style="font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Deskripsi Masalah</div>
      <div style="color:#1e293b;line-height:1.7;font-size:13px;"><?= nl2br(htmlspecialchars($detail['deskripsi'])) ?></div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ===== TABEL DAFTAR KOMPLAIN ===== -->
<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title" style="color:#1e293b;">📋 Daftar Komplain</span>
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
      <?php foreach(['semua'=>'Semua','baru'=>'Baru','diproses'=>'Diproses','selesai'=>'Selesai'] as $v=>$l): ?>
      <a href="admin.php?page=komplain&status=<?= $v ?>"
         class="admin-btn admin-btn-sm <?= $filter_status===$v?'admin-btn-primary':'admin-btn-outline' ?>"><?= $l ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php if(empty($filtered)): ?>
  <div class="admin-empty"><span class="admin-empty-icon">📭</span>Tidak ada komplain dengan filter ini</div>
  <?php else: ?>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="color:#475569;">No. Tiket</th>
          <th style="color:#475569;">Nama</th>
          <th style="color:#475569;">Kamar</th>
          <th style="color:#475569;">Kategori</th>
          <th style="color:#475569;">Status</th>
          <th style="color:#475569;">Foto</th>
          <th style="color:#475569;width:100px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($filtered as $k): ?>
      <tr>
        <td style="font-family:monospace;font-size:10px;font-weight:700;color:#475569;"><?= $k['no_tiket'] ?></td>
        <td style="font-weight:600;color:#1e293b;"><?= htmlspecialchars($k['nama']) ?></td>
        <td style="font-weight:700;color:#0F6E56;"><?= $k['no_kamar'] ?></td>
        <td style="font-size:11px;color:#475569;"><?= $k['kategori'] ?></td>
        <td>
          <!-- Form Update Status langsung di tabel -->
          <form method="post" style="display:inline;">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" value="<?= $k['id'] ?>">
            <select name="status" onchange="this.form.submit()"
              style="padding:2px 6px;font-size:11px;border-radius:6px;background:#f1f5f9;border:1px solid #cbd5e1;color:#1e293b;cursor:pointer;">
              <option value="baru"     <?= $k['status']==='baru'?'selected':'' ?>>Baru</option>
              <option value="diproses" <?= $k['status']==='diproses'?'selected':'' ?>>Diproses</option>
              <option value="selesai"  <?= $k['status']==='selesai'?'selected':'' ?>>Selesai</option>
            </select>
          </form>
        </td>
        <td>
          <?php if ($k['foto']): ?>
            <a href="uploads/<?= $k['foto'] ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm">📷</a>
          <?php else: ?>
            <span style="color:#94a3b8;">-</span>
          <?php endif; ?>
        </td>
        <td>
          <div style="display:flex;gap:4px;flex-wrap:wrap;">
            <a href="admin.php?page=komplain&detail=<?= $k['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm" title="Detail">👁️</a>
            <form method="post" style="display:inline;" onsubmit="return confirm('Hapus komplain ini?')">
              <input type="hidden" name="action" value="hapus_komplain">
              <input type="hidden" name="id" value="<?= $k['id'] ?>">
              <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">🗑️</button>
            </form>
          </div>
        </td>
      </tr>
      <!-- Tampilkan deskripsi singkat di baris berikutnya -->
      <?php if(!empty($k['deskripsi'])): ?>
      <tr>
        <td colspan="7" style="padding:4px 14px 10px;font-size:11px;color:#475569;background:#f8fafc;">
          <strong style="color:#1e293b;">Deskripsi:</strong> <?= htmlspecialchars(mb_strimwidth($k['deskripsi'], 0, 120, '...')) ?>
        </td>
      </tr>
      <?php endif; ?>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>