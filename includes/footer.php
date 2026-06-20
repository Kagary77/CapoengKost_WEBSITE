</main>
  </div><!-- end main-content -->
</div><!-- end app-layout -->

<div id="toast"></div>

<script>
// ===== SIDEBAR MOBILE =====
function openSidebar()  { document.getElementById('sidebar').classList.add('open');  document.getElementById('overlay').classList.add('show'); }
function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('overlay').classList.remove('show'); }

// ===== TOAST =====
function showToast(msg, type = '') {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'show ' + type;
  setTimeout(() => t.className = '', 3500);
}
<?php if (isset($_SESSION['toast_msg'])): ?>
showToast('<?= addslashes($_SESSION['toast_msg']) ?>', '<?= $_SESSION['toast_type'] ?? '' ?>');
<?php unset($_SESSION['toast_msg'], $_SESSION['toast_type']); endif; ?>

// ===== MODAL: LOGIN REQUIRED =====
function openLoginModal(redirectPage) {
  if (document.getElementById('modal_redirect'))
    document.getElementById('modal_redirect').value = redirectPage || 'beranda';
  document.getElementById('loginModal').classList.add('show');
}
function closeLoginModal() {
  document.getElementById('loginModal').classList.remove('show');
}
function requireLogin(page) {
  openLoginModal(page);
}

// ===== MODAL: BOOKING KAMAR TAMU =====
function openBookingModalTamu(nama, harga) {
  document.getElementById('booking_tamu_kamar_nama').value = nama;
  document.getElementById('booking_tamu_kamar_harga').value = harga;
  document.getElementById('bookingTamuName').textContent = '📅 Booking ' + nama;
  document.getElementById('bookingTamuHarga').textContent = 'Rp ' + Number(harga).toLocaleString('id-ID') + ' / bulan';
  document.getElementById('bookingModalTamu').classList.add('show');
}
function closeBookingModalTamu() {
  document.getElementById('bookingModalTamu').classList.remove('show');
}

// ===== MODAL: BOOKING KAMAR (LAMA) =====
function openBookingModal(id, nama, harga) {
  document.getElementById('booking_kamar_id').value    = id;
  document.getElementById('booking_kamar_nama').value  = nama;
  document.getElementById('booking_kamar_harga').value = harga;
  document.getElementById('bookingKamarName').textContent  = '📅 Pesan ' + nama;
  document.getElementById('bookingKamarHarga').textContent = 'Rp ' + Number(harga).toLocaleString('id-ID') + ' / bulan';
  document.getElementById('bookingModal').classList.add('show');
}
function closeBookingModal() {
  document.getElementById('bookingModal').classList.remove('show');
}

// ===== MODAL: PAYMENT =====
function openPayModal(id, nama, bulan, jumlah) {
  if (!document.getElementById('pay-id')) return;
  document.getElementById('pay-id').value           = id;
  document.getElementById('pay-nama').textContent   = nama;
  document.getElementById('pay-bulan').textContent  = bulan;
  document.getElementById('pay-jumlah').textContent = 'Rp ' + Number(jumlah).toLocaleString('id-ID');
  document.getElementById('payModal').classList.add('show');
}
function closePayModal() {
  const m = document.getElementById('payModal');
  if (m) m.classList.remove('show');
}

// ===== MODAL: KOMPLAIN BERHASIL DIKIRIM =====
function closeKomplainModal() {
  const m = document.getElementById('komplainSuccessModal');
  if (m) m.classList.remove('show');
}
function copyTiketKomplain() {
  const el  = document.getElementById('tiketModalValue');
  const btn = document.getElementById('btnCopyTiket');
  if (!el) return;
  navigator.clipboard.writeText(el.textContent.trim()).then(() => {
    if (!btn) return;
    const original = btn.innerHTML;
    btn.innerHTML = '✅ Tersalin!';
    setTimeout(() => btn.innerHTML = original, 1800);
  });
}

// ===== FILE UPLOAD =====
function handleFileUpload(input, labelId) {
  const file = input.files[0];
  if (file) {
    const el = document.getElementById(labelId);
    if (el) el.innerHTML = '<strong>' + file.name + '</strong><br>' + Math.round(file.size/1024) + ' KB';
  }
}

// Tutup modal kalau klik di luar box
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('show');
  });
});
</script>
</body>
</html>