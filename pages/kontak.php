<?php
// ============================================================
//  VIEW: Kontak (Publik)
//  Logika POST sudah ditangani di actions/kontak.php
//  (dijalankan sebelum file ini, sebelum HTML dicetak).
// ============================================================

$sent = isset($_GET['sent']);
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

  <!-- Info Kontak -->
  <div>
    <div class="card mb-2">
      <div class="card-header"><span class="card-title">📍 Lokasi & Alamat</span></div>
      <div class="card-body">
        <div style="background:linear-gradient(135deg,var(--teal-light),#9FE1CB);height:200px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;margin-bottom:1rem;">
          <span style="font-size:2.5rem;">📍</span>
          <span style="font-weight:600;color:var(--teal-dark);">Kost Putri Capoeng</span>
          <span style="font-size:12px;color:var(--teal);">Banyuwangi, Jawa Timur</span>
          <a href="https://www.google.com/maps?q=-8.215173958969775,114.34980363524744" target="_blank" class="btn btn-outline btn-sm" style="margin-top:4px;">Buka Google Maps ↗</a>
        </div>
        <p class="text-sm text-muted">Berlokasi strategis di Banyuwangi, mudah dijangkau dari pusat kota dan area pendidikan.</p>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">☎️ Informasi Kontak</span></div>
      <div class="card-body" style="padding:0;">
        <?php
        $contacts = [
          ['📞','Telepon / WhatsApp','0812-3456-7890 (Bpk. Luky)'],
          ['👤','Pengelola','Bpk. Lukyta Surawijaya & Ibu Ina'],
          ['📍','Alamat','Banyuwangi, Jawa Timur 68400'],
          ['🕐','Jam Operasional','Senin – Sabtu, 08.00 – 20.00 WIB'],
          ['📧','Email','kostputricapoeng@gmail.com'],
        ];
        foreach($contacts as $c):
        ?>
        <div style="display:flex;align-items:center;gap:12px;padding:11px 1.25rem;border-bottom:1px solid var(--border);">
          <div style="width:36px;height:36px;border-radius:9px;background:var(--teal-light);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;"><?= $c[0] ?></div>
          <div>
            <div style="font-size:10px;font-weight:600;color:var(--text-muted);letter-spacing:0.3px;"><?= $c[1] ?></div>
            <div style="font-size:13px;font-weight:600;color:var(--text);"><?= $c[2] ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <div style="padding:1rem 1.25rem;">
          <a href="https://wa.me/6281234567890" target="_blank" class="btn" style="width:100%;background:#25D366;color:white;border-radius:8px;padding:10px;font-size:13px;font-weight:600;display:block;text-align:center;">
            💬 Chat WhatsApp Sekarang
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Form Pesan -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">✉️ Kirim Pesan</span>
    </div>
    <div class="card-body">
      <?php if($sent): ?>
      <div class="alert alert-success mb-2">✅ Pesan berhasil dikirim! Pengelola akan menghubungi kamu segera.</div>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="action" value="kirim_pesan">
        <div class="form-group">
          <label>Nama Lengkap *</label>
          <input type="text" name="nama" placeholder="Nama kamu" value="<?= htmlspecialchars($_SESSION['user_nama'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>No. WhatsApp / Telepon *</label>
          <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($_SESSION['user_hp'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Topik / Subjek *</label>
          <select name="topik" required>
            <option value="">— Pilih topik —</option>
            <option>Informasi Kamar & Sewa</option>
            <option>Survei / Kunjungan Lokasi</option>
            <option>Pertanyaan Fasilitas</option>
            <option>Informasi Pembayaran</option>
            <option>Lainnya</option>
          </select>
        </div>
        <div class="form-group">
          <label>Pesan *</label>
          <textarea name="pesan" rows="5" placeholder="Tulis pesan atau pertanyaanmu di sini..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Kirim Pesan →</button>
      </form>
      <hr class="divider">
      <div style="background:var(--teal-xlight);border-radius:10px;padding:1rem;font-size:12px;color:var(--teal-dark);">
        <strong>💡 Tips:</strong> Untuk respon lebih cepat, gunakan tombol WhatsApp di atas. Pengelola biasanya membalas dalam 1–2 jam pada hari kerja.
      </div>
    </div>
  </div>

</div>

<!-- FAQ -->
<div class="card mt-3">
  <div class="card-header"><span class="card-title">❓ Pertanyaan Umum (FAQ)</span></div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
      <?php
      $faq = [
        ['Berapa harga sewa kamar?','Kamar Standard Rp 450.000/bulan, Kamar Premium (AC) Rp 850.000/bulan. Harga sudah termasuk WiFi dan fasilitas umum.'],
        ['Apakah bisa survei terlebih dahulu?','Tentu! Hubungi kami lewat WhatsApp untuk mengatur jadwal kunjungan ke lokasi.'],
        ['Kapan batas pembayaran sewa?','Pembayaran sewa paling lambat tanggal 5 setiap bulannya.'],
        ['Bagaimana cara melapor kerusakan?','Gunakan menu Komplain di sidebar. Tidak perlu login, cukup isi nama kamar dan deskripsi masalah.'],
      ];
      foreach($faq as $f):
      ?>
      <div style="background:var(--bg);border-radius:10px;padding:1rem;">
        <div style="font-weight:700;font-size:12px;margin-bottom:5px;color:var(--teal-dark);"><?= $f[0] ?></div>
        <div style="font-size:12px;color:var(--text-muted);"><?= $f[1] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>