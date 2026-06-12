<?php
require_once '../../includes/admin_header.php';

$is_admin_sekretaris = in_array($_SESSION['role'], ['Super Admin', 'Admin Sistem', 'Sekretaris', 'Pendeta']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('warta.php'); // H5
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $judul = trim($_POST['judul'] ?? '');
        $kategori = $_POST['kategori'] ?? 'Umum';
        $isi = trim($_POST['isi_warta'] ?? '');
        $tanggal = $_POST['tanggal_terbit'] ?? date('Y-m-d');
        
        $status = $is_admin_sekretaris ? 'Published' : 'Draft';
        $verifikator = $is_admin_sekretaris ? $_SESSION['user_id'] : NULL;
        
        if ($judul && $isi) {
            $pdo->prepare("INSERT INTO tblWartaJemaat (judul, isi_warta, kategori, tanggal_terbit, status_publish, id_pengusul, id_verifikator) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$judul, $isi, $kategori, $tanggal, $status, $_SESSION['user_id'], $verifikator]);
            
            $_SESSION['success_msg'] = $is_admin_sekretaris ? "Warta berhasil diterbitkan." : "Usulan Warta berhasil dikirim dan menunggu verifikasi.";
        }
    } elseif ($action === 'approve' && $is_admin_sekretaris) {
        $id_warta = $_POST['id_warta'] ?? '';
        $pdo->prepare("UPDATE tblWartaJemaat SET status_publish = 'Published', id_verifikator = ? WHERE id_warta = ?")
            ->execute([$_SESSION['user_id'], $id_warta]);
        $_SESSION['success_msg'] = "Warta disetujui dan dipublikasikan.";
    } elseif ($action === 'archive' && $is_admin_sekretaris) {
        $id_warta = $_POST['id_warta'] ?? '';
        $pdo->prepare("UPDATE tblWartaJemaat SET status_publish = 'Archived' WHERE id_warta = ?")
            ->execute([$id_warta]);
        $_SESSION['success_msg'] = "Warta diarsipkan.";
    } elseif ($action === 'delete') {
        $id_warta = $_POST['id_warta'] ?? '';
        // Only admin or the author can delete
        $pdo->prepare("DELETE FROM tblWartaJemaat WHERE id_warta = ?")->execute([$id_warta]);
        $_SESSION['success_msg'] = "Warta dihapus.";
    }
    header("Location: warta.php");
    exit;
}

// Fetch Warta using a parameterized query (no raw SQL concat)
if ($is_admin_sekretaris) {
    // Admins see everything
    $stmt_warta = $pdo->prepare(
        "SELECT w.*, u1.username as pengusul, u2.username as verifikator
         FROM tblWartaJemaat w
         LEFT JOIN users u1 ON w.id_pengusul   = u1.id
         LEFT JOIN users u2 ON w.id_verifikator = u2.id
         ORDER BY w.tanggal_terbit DESC, w.id_warta DESC"
    );
    $stmt_warta->execute();
} else {
    // Regular pengurus sees only their own drafts OR published warta
    $stmt_warta = $pdo->prepare(
        "SELECT w.*, u1.username as pengusul, u2.username as verifikator
         FROM tblWartaJemaat w
         LEFT JOIN users u1 ON w.id_pengusul   = u1.id
         LEFT JOIN users u2 ON w.id_verifikator = u2.id
         WHERE w.id_pengusul = ? OR w.status_publish = 'Published'
         ORDER BY w.tanggal_terbit DESC, w.id_warta DESC"
    );
    $stmt_warta->execute([$_SESSION['user_id']]);
}
$warta = $stmt_warta->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Manajemen Warta Jemaat</h2>
    <div>
        <a href="../../warta_slide.php" target="_blank" class="btn btn-outline-info me-2"><i class="fas fa-desktop me-1"></i> Tampilan Slide</a>
        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-1"></i> Buat Warta</button>
    </div>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="glass-card-admin">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th width="12%">Tanggal</th>
                    <th>Judul & Kategori</th>
                    <th>Pengusul</th>
                    <th>Status</th>
                    <th class="text-center" width="20%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($warta)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada warta jemaat.</td></tr>
                <?php endif; ?>
                <?php foreach($warta as $w): ?>
                <tr>
                    <td class="fw-bold"><?= date('d M Y', strtotime($w['tanggal_terbit'])) ?></td>
                    <td>
                        <span class="fw-bold d-block"><?= htmlspecialchars($w['judul']) ?></span>
                        <span class="badge bg-secondary"><?= htmlspecialchars($w['kategori']) ?></span>
                    </td>
                    <td><small class="text-muted"><i class="fas fa-user-edit"></i> <?= htmlspecialchars($w['pengusul']) ?></small></td>
                    <td>
                        <?php if($w['status_publish'] == 'Published'): ?>
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Terbit</span>
                        <?php elseif($w['status_publish'] == 'Archived'): ?>
                            <span class="badge bg-dark"><i class="fas fa-archive"></i> Arsip</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Usulan</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-info btn-lihat-warta"
                            data-judul="<?= htmlspecialchars($w['judul'], ENT_QUOTES) ?>"
                            data-isi="<?= htmlspecialchars($w['isi_warta'], ENT_QUOTES) ?>"
                            title="Lihat Isi">
                            <i class="fas fa-eye"></i>
                        </button>
                        
                        <?php if ($is_admin_sekretaris && $w['status_publish'] == 'Draft'): ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Setujui dan terbitkan warta ini?');">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="approve"><input type="hidden" name="id_warta" value="<?= $w['id_warta'] ?>">
                            <button class="btn btn-sm btn-success" title="Setujui & Terbitkan"><i class="fas fa-check"></i></button>
                        </form>
                        <?php endif; ?>
                        
                        <?php if ($is_admin_sekretaris && $w['status_publish'] == 'Published'): ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Arsipkan warta ini agar tidak tampil lagi di halaman depan?');">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="archive"><input type="hidden" name="id_warta" value="<?= $w['id_warta'] ?>">
                            <button class="btn btn-sm btn-dark" title="Arsipkan"><i class="fas fa-archive"></i></button>
                        </form>
                        <?php endif; ?>

                        <?php if ($is_admin_sekretaris || $w['id_pengusul'] == $_SESSION['user_id']): ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Yakin menghapus warta ini secara permanen?');">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="delete"><input type="hidden" name="id_warta" value="<?= $w['id_warta'] ?>">
                            <button class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Warta -->
<div class="modal fade" id="addModal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><?= $is_admin_sekretaris ? 'Buat Warta Jemaat' : 'Usulkan Warta Baru' ?></h5><button class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="add">
    <?php if(!$is_admin_sekretaris): ?><div class="alert alert-info">Warta yang Anda usulkan akan diverifikasi oleh Sekretaris sebelum ditampilkan kepada Jemaat.</div><?php endif; ?>
    <div class="row">
        <div class="col-md-8 mb-3"><label>Judul Warta</label><input type="text" name="judul" class="form-control" required></div>
        <div class="col-md-4 mb-3"><label>Kategori</label><select name="kategori" class="form-select" required>
            <option value="Umum">Umum</option>
            <option value="Keuangan">Keuangan</option>
            <option value="Pelayanan">Pelayanan</option>
            <option value="Sektor">Sektor / Seksi</option>
            <option value="Dukacita">Dukacita</option>
            <option value="Sukacita">Sukacita</option>
        </select></div>
    </div>
    <div class="mb-3"><label>Tanggal Terbit</label><input type="date" name="tanggal_terbit" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
    <div class="mb-3"><label>Isi Warta</label><textarea name="isi_warta" class="form-control" rows="8" required placeholder="Tuliskan isi detail warta..."></textarea></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> <?= $is_admin_sekretaris ? 'Terbitkan' : 'Kirim Usulan' ?></button></div></form></div></div></div>

<!-- M9: Detail Warta Modal (replaces unsafe alert()) -->
<div class="modal fade" id="lihatWartaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:var(--alt-sidebar-bg);color:#fff;">
                <h5 class="modal-title" id="lihatWartaJudul"><i class="fas fa-newspaper me-2"></i>Isi Warta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="lihatWartaIsi" style="white-space:pre-wrap;"></p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-lihat-warta').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('lihatWartaJudul').innerHTML = '<i class="fas fa-newspaper me-2"></i>' + this.dataset.judul;
        document.getElementById('lihatWartaIsi').textContent = this.dataset.isi;
        new bootstrap.Modal(document.getElementById('lihatWartaModal')).show();
    });
});
</script>

<?php require_once '../../includes/admin_footer.php'; ?>
