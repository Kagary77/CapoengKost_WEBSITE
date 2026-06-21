<?php
/**
 * ================================================================
 *  FOOTER ADMIN
 *  - Berisi script penutup, fungsi sidebar mobile, toast notifikasi,
 *    dan utilitas JavaScript untuk modal/konfirmasi.
 * ================================================================
 */
?>
</main>
  </div><!-- end admin-main -->
</div><!-- end admin-layout -->

<div id="toast"></div>

<script>
// ===== SIDEBAR MOBILE (Toggle) =====
function openSidebar()  { document.getElementById('sidebar').classList.add('open');  document.getElementById('overlay').classList.add('show'); }
function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('overlay').classList.remove('show'); }

// ===== TOAST NOTIFICATION =====
function showToast(msg, type = '') {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'show ' + type;
  setTimeout(() => t.className = '', 3500);
}

// Tampilkan toast dari session (jika ada)
<?php if ($toast_msg): ?>
showToast('<?= addslashes($toast_msg) ?>', '<?= $toast_type ?>');
<?php endif; ?>

// ===== KONFIRMASI HAPUS / AKSI BERBAHAYA =====
function confirmAction(msg, formId) {
  if (confirm(msg)) {
    document.getElementById(formId).submit();
  }
}

// ===== AUTO DISMISS ALERT (4 detik) =====
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .5s'; setTimeout(() => el.remove(), 500); }, 4000);
});

// ===== TUTUP MODAL SAAT KLIK DI LUAR BOX =====
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('show');
  });
});
</script>
</body>
</html>