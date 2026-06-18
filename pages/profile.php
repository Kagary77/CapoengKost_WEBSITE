<?php
$user = [
    'nama'      => $_SESSION['user_nama']      ?? 'Pengguna',
    'email'     => $_SESSION['user_email']     ?? 'user@email.com',
    'no_hp'     => $_SESSION['user_hp']        ?? '-',
    'no_kamar'  => $_SESSION['user_kamar']     ?? '-',
    'alamat'    => $_SESSION['user_alamat']    ?? '-',
    'pekerjaan' => $_SESSION['user_pekerjaan'] ?? '-',
    'role'      => $_SESSION['user_role']      ?? 'penghuni',
];
$inisial = strtoupper(implode('', array_map(fn($w)=>$w[0], explode(' ', $user['nama']))));
$inisial = substr($inisial, 0, 2);

$komplain_all  = getKomplainAll();
$komplain_user = array_filter($komplain_all, fn($k) => strtolower($k['no_kamar']) === strtolower($user['no_kamar']));
$trans_all     = getTransaksiAll();
$trans_user    = array_filter($trans_all, fn($t) => strtolower($t['no_kamar']) === strtolower($user['no_kamar']));
$trans_lunas   = array_filter($trans_user, fn($t) => $t['status'] === 'lunas');
?>

<div class="profile-layout">

  <div>
    <div class="profile-sidebar-card">
      <div class="profile-avatar-big"><?= $inisial ?></div>
      <div class="profile-name"><?= htmlspecialchars($user['nama']) ?></div>
      <div class="profile-role"><span class="badge badge-<?= $user['role'] === 'admin' ? 'admin' : 'penghuni' ?>"><?= ucfirst($user['role']) ?></span></div>

      <hr class="divider">

      <div class="profile-info-row">
        <span>Email</span>
        <span><?= htmlspecialchars($user['email']) ?></span>
      </div>
      <div class="profile-info-row">
        <span>No. Kamar</span>
        <span style="color:#0F6E56;"><?= $user['no_kamar'] ?></span>
      </div>
      <div class="profile-info-row">
        <span>No. HP</span>
        <span><?= $user['no_hp'] ?></span>
      </div>
      <div class="profile-info-row">
        <span>Pekerjaan</span>
        <span><?= $user['pekerjaan'] ?></span>
      </div>
    </div>

    <div class="card mt-2">
      <div class="card-header"><span class="card-title">📊 Ringkasan Aktivitas</span></div>
      <div class="card-body" style="padding:0;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 1.25rem;border-bottom:1px solid var(--border);">
          <span class="text-sm text-muted">Total Komplain</span>
          <strong style="color:#1a1a1a;"><?= count($komplain_user) ?></strong>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 1.25rem;border-bottom:1px solid var(--border);">
          <span class="text-sm text-muted">Transaksi Lunas</span>
          <strong style="color:#0F6E56;"><?= count($trans_lunas) ?></strong>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 1.25rem;">
          <span class="text-sm text-muted">Status Kamar</span>
          <span class="badge badge-tersedia">Aktif</span>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="card mb-3">
      <div class="card-header">
        <span class="card-title">✏️ Edit Data Diri</span>
      </div>
      <div class="card-body">
        <?php if(isset($error)): ?><div class="alert alert-error mb-2"><?= $error ?></div><?php endif; ?>
        <form method="post">
          <input type="hidden" name="action" value="update_profil">
          <div class="form-row">
            <div class="form-group">
              <label>Nama Lengkap *</label>
              <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
            <div class="form-group">
              <label>No. WhatsApp</label>
              <input type="text" name="no_hp" value="<?= htmlspecialchars($user['no_hp'] === '-' ? '' : $user['no_hp']) ?>" placeholder="08xxxxxxxxxx">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Pekerjaan / Status</label>
              <select name="pekerjaan">
                <option value="Mahasiswa"  <?= $user['pekerjaan']==='Mahasiswa' ?'selected':'' ?>>Mahasiswa</option>
                <option value="Karyawan"   <?= $user['pekerjaan']==='Karyawan'  ?'selected':'' ?>>Karyawan</option>
                <option value="Wiraswasta" <?= $user['pekerjaan']==='Wiraswasta'?'selected':'' ?>>Wiraswasta</option>
                <option value="Lainnya"    <?= $user['pekerjaan']==='Lainnya'   ?'selected':'' ?>>Lainnya</option>
              </select>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:var(--bg);color:var(--text-muted);">
            </div>
          </div>
          <div class="form-group">
            <label>Alamat Asal</label>
            <textarea name="alamat" rows="2" placeholder="Alamat asal kamu"><?= htmlspecialchars($user['alamat'] === '-' ? '' : $user['alamat']) ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">🔒 Ganti Password</span></div>
      <div class="card-body">
        <?php if(isset($pass_error)): ?><div class="alert alert-error mb-2"><?= $pass_error ?></div><?php endif; ?>
        <form method="post">
          <input type="hidden" name="action" value="ganti_password">
          <div class="form-group">
            <label>Password Lama *</label>
            <input type="password" name="old_pass" placeholder="••••••••" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Password Baru *</label>
              <input type="password" name="new_pass" placeholder="Min. 6 karakter" required>
            </div>
            <div class="form-group">
              <label>Konfirmasi Password *</label>
              <input type="password" name="confirm_pass" placeholder="Ulangi password baru" required>
            </div>
          </div>
          <button type="submit" class="btn btn-outline">🔒 Perbarui Password</button>
        </form>
      </div>
    </div>
  </div>
</div>