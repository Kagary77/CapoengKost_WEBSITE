<?php
// Halaman ini ditampilkan saat tamu mencoba akses halaman terlindungi
$page_icons = [
    'komplain'  => ['🔒', 'Fitur Komplain untuk Penghuni', 'Ajukan dan pantau status pengaduan fasilitas kost kamu.'],
    'transaksi' => ['💳', 'Riwayat Transaksi untuk Penghuni', 'Lihat tagihan dan riwayat pembayaran sewa kamu.'],
    'profil'    => ['👤', 'Data Diri untuk Penghuni', 'Kelola informasi profil dan ganti password akun kamu.'],
];
[$icon, $title, $desc] = $page_icons[$activePage] ?? ['🔒', 'Fitur Ini untuk Penghuni', 'Halaman ini hanya tersedia untuk penghuni terdaftar.'];
?>

<div class="login-required-banner">
  <div class="lrb-icon"><?= $icon ?></div>
  <h3><?= $title ?></h3>
  <p><?= $desc ?><br>Silakan login untuk mengakses fitur ini.</p>
  <a href="#" onclick="openLoginModal('<?= $activePage ?>'); return false;" class="lrb-btn">
    🔑 Login Sekarang
  </a>
</div>

<!-- Preview kamar bagi yang belum login (tetap berguna) -->
<div class="card">
  <div class="card-header">
    <span class="card-title">💡 Sementara, kamu masih bisa:</span>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
      <a href="index.php?page=kamar" style="text-decoration:none;">
        <div style="background:var(--teal-xlight);border:1px solid #9FE1CB;border-radius:12px;padding:1rem;text-align:center;">
          <div style="font-size:1.75rem;margin-bottom:6px;">🛏️</div>
          <div style="font-weight:700;font-size:13px;color:var(--teal-dark);">Lihat Katalog Kamar</div>
          <div style="font-size:11px;color:var(--teal);margin-top:3px;">Cek ketersediaan & harga</div>
        </div>
      </a>
      <a href="index.php?page=kontak" style="text-decoration:none;">
        <div style="background:var(--amber-light);border:1px solid #FAC775;border-radius:12px;padding:1rem;text-align:center;">
          <div style="font-size:1.75rem;margin-bottom:6px;">📞</div>
          <div style="font-weight:700;font-size:13px;color:#633806;">Hubungi Pengelola</div>
          <div style="font-size:11px;color:#92400e;margin-top:3px;">Tanya via WhatsApp</div>
        </div>
      </a>
      <a href="index.php?page=beranda" style="text-decoration:none;">
        <div style="background:#f3f4f6;border:1px solid var(--border);border-radius:12px;padding:1rem;text-align:center;">
          <div style="font-size:1.75rem;margin-bottom:6px;">🏠</div>
          <div style="font-weight:700;font-size:13px;color:var(--text);">Kembali ke Beranda</div>
          <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">Informasi umum kost</div>
        </div>
      </a>
    </div>
  </div>
</div>
