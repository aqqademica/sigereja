</main>
<footer class="mt-auto py-3 text-center">
    <div class="container">
        <span><i class="fas fa-church me-1" style="color:hsl(35,90%,58%)"></i>
        Sistem Informasi Gereja ABC&mdash;Dikembangkan &copy;Jonathan Steve Hasibuan <?= date('Y') ?>
        &nbsp;&bull;&nbsp; Untuk Skripsi Sistem Informasi 2026 &mdash; </span>
    </div>
</footer>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mark-all-read for public navbar
document.querySelector('.mark-read-link')?.addEventListener('click', function(e) {
    e.preventDefault();
    fetch('actions/mark_notif_read.php').then(() => location.reload());
});
</script>
</body>
</html>
