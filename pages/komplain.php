<?php
$success    = isset($_GET['success']);
$last_tiket = $_SESSION['last_tiket'] ?? null;
if ($success) unset($_SESSION['last_tiket']);

$komplain_list = getKomplainAll();
?>

<div class="komplain-layout">

  <div>
    <?php if ($success && $last_tiket): ?>
    <div class="alert alert-success mb-2">✅ Pengaduan berhasil dikirim!</div>
    <div class="ticket-box mb-3">
      <div class="ticket-label">Nomor Tiket Kamu</div>
      <div class="ticket-no"><?= $last_tiket ?></div>
      <div class="text-sm text-muted mt-1">Simpan nomor ini untuk memantau status pengaduan</div>
    </div>
    <?php endif; ?>

    <div class="card mb-2">
      <div class="card-header"><span class="card-title">📋 Riwayat Komplain</span></div>
      <?php if (empty($komplain_list)): ?>
      <div class="empty-state"><span class="empty-icon">📭</span><h4>Belum ada komplain</h4><p>Gunakan form di sebelah kanan untuk melapor</p></div>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>No. Tiket</th>
              <th>Nama</th>
              <th>Kamar</th>
              <th>Kategori</th>
              <th>Status</th>
              <th>Tanggal</th>
              <?php if(isAdmin()): ?><th>Aksi</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
          <?php foreach($komplain_list as $k): ?>
          <tr>
            <td style="font-family:monospace;font-size:11px;font-weight:700;color:#475569;"><?= $k['no_tiket'] ?></td>
            <td style="color:#1a1a1a;"><?= htmlspecialchars($k['nama']) ?></td>
            <td><strong style="color:#0F6E56;"><?= $k['no_kamar'] ?></strong></td>
            <td class="text-sm text-muted"><?= $k['kategori'] ?></td>
            <td>
              <?php
              $badge = ['baru'=>'badge-baru','diproses'=>'badge-diproses','selesai'=>'badge-selesai'];
              $label = ['baru'=>'Baru','diproses'=>'Diproses','selesai'=>'Selesai'];
              ?>
              <span class="badge <?= $badge[$k['status']] ?? '' ?>"><?= $label[$k['status']] ?? $k['status'] ?></span>
            </td>
            <td class="text-sm text-muted"><?= $k['created_at'] ?></td>
            <?php if(isAdmin()): ?>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" value="<?= $k['id'] ?>">
                <select name="status" onchange="this.form.submit()" style="padding:4px 6px;font-size:11px;border:1px solid var(--border);border-radius:6px;background:white;cursor:pointer;color:#1a1a1a;">
                  <option value="baru"      <?= $k['status']==='baru'     ?'selected':'' ?>>Baru</option>
                  <option value="diproses"  <?= $k['status']==='diproses' ?'selected':'' ?>>Diproses</option>
                  <option value="selesai"   <?= $k['status']==='selesai'  ?'selected':'' ?>>Selesai</option>
                </select>
              </form>
            </td>
            <?php endif; ?>
          </tr>
          <?php if(!empty($k['deskripsi'])): ?>
          <tr style="background:var(--bg);">
            <td colspan="<?= isAdmin()?7:6 ?>" style="padding:6px 14px 10px;font-size:12px;color:var(--text-muted);">
              <strong style="color:#1a1a1a;">Deskripsi:</strong> <?= htmlspecialchars($k['deskripsi']) ?>
            </td>
          </tr>
          <?php endif; ?>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

    <div class="card" style="background:var(--teal-xlight);">
      <div class="card-body">
        <div style="font-size:12px;font-weight:700;color:var(--teal-dark);margin-bottom:10px;">🔒 Login — Langsung Lapor</div>
        <div class="info-steps">
          <?php
          $steps = [
            ['Isi data kamar',       'Nama dan nomor kamar yang bermasalah'],
            ['Pilih kategori',       'Jenis kerusakan atau keluhan'],
            ['Deskripsikan masalah', 'Detail agar pengelola mudah memahami'],
            ['Lampirkan foto',       'Opsional, membantu proses perbaikan'],
          ];
          foreach($steps as $i => $s): ?>
          <div class="step">
            <div class="step-num"><?= $i+1 ?></div>
            <div class="step-text">
              <strong><?= $s[0] ?></strong>
              <span><?= $s[1] ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">📝 Form Pengaduan Fasilitas</span>
      <span class="badge badge-baru">No Login Required</span>
    </div>
    <div class="card-body">
      <?php if(isset($error)): ?>
      <div class="alert alert-error mb-2"><?= $error ?></div>
      <?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="submit_komplain">
        <div class="form-row">
          <div class="form-group">
            <label>Nama Penghuni *</label>
            <input type="text" name="nama" placeholder="Nama lengkap" value="<?= htmlspecialchars($_POST['nama'] ?? ($_SESSION['user_nama'] ?? '')) ?>" required>
          </div>
          <div class="form-group">
            <label>Nomor / Nama Kamar *</label>
            <input type="text" name="no_kamar" placeholder="Contoh: A1, B2" required>
          </div>
        </div>
        <div class="form-group">
          <label>No. WhatsApp <span class="text-muted">(opsional)</span></label>
          <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($_POST['no_hp'] ?? ($_SESSION['user_hp'] ?? '')) ?>">
        </div>
        <div class="form-group">
          <label>Kategori Masalah *</label>
          <select name="kategori" required>
            <option value="">— Pilih kategori —</option>
            <?php foreach(['Kerusakan Listrik','Kerusakan Air / Kamar Mandi','Kerusakan Fasilitas Kamar','Masalah Kebersihan','Masalah WiFi / Internet','Masalah AC','Keamanan','Lainnya'] as $kat): ?>
            <option><?= $kat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Deskripsi Masalah *</label>
          <textarea name="deskripsi" rows="4" placeholder="Jelaskan detail kerusakan atau keluhan yang kamu alami..." required></textarea>
        </div>
        <div class="form-group">
          <label>Foto Dokumentasi <span class="text-muted">(opsional)</span></label>
          <div class="file-drop">
            <input type="file" name="foto" accept="image/*" onchange="handleFileUpload(this,'fileLabel')">
            <span class="file-drop-icon">📷</span>
            <div class="file-drop-text" id="fileLabel">
              <strong>Klik untuk upload foto</strong><br>JPG, PNG, WEBP — maks 5MB
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Kirim Pengaduan →</button>
        <div class="text-sm text-muted" style="text-align:center;margin-top:8px;">Keluhan kamu akan langsung diterima dan diproses pengelola</div>
      </form>
    </div>
  </div>

</div>

<?php if ($success && $last_tiket): ?>
<div class="modal-overlay show" id="komplainSuccessModal">
  <div class="modal-box" style="max-width:380px;position:relative;">
    <button type="button" class="modal-close-x" onclick="closeKomplainModal()" aria-label="Tutup">✕</button>
    <div class="modal-icon-success">✅</div>
    <div class="modal-title">Pengaduan Berhasil Dikirim!</div>
    <div class="modal-desc">Komplain kamu sudah kami terima dan akan segera ditindaklanjuti oleh pengelola.</div>
    <div class="ticket-box" style="margin-top:0;">
      <div class="ticket-label">Nomor Tiket Kamu</div>
      <div class="ticket-no" id="tiketModalValue"><?= htmlspecialchars($last_tiket) ?></div>
      <div class="text-sm text-muted mt-1">Simpan nomor ini untuk memantau status pengaduan</div>
      <button type="button" class="btn-copy" id="btnCopyTiket" onclick="copyTiketKomplain()">📋 Salin No. Tiket</button>
    </div>
    <div class="modal-actions" style="margin-top:1.25rem;">
      <button type="button" onclick="closeKomplainModal()" class="btn btn-primary">Tutup</button>
    </div>
  </div>
</div>
<?php endif; ?>