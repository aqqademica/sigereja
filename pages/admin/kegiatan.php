<?php
require_once '../../includes/admin_header.php';
require_once '../../actions/notifikasi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('kegiatan.php'); // H5
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $nama     = trim($_POST['nama_kegiatan'] ?? '');
        $tgl      = $_POST['tanggal_pelaksanaan'] ?? '';
        $tempat   = trim($_POST['tempat'] ?? '');
        $status   = $_POST['status'] ?? 'Akan Dilaksanakan';
        if ($nama && $tgl) {
            $pdo->prepare("INSERT INTO tblKegiatan (nama_kegiatan, tanggal_pelaksanaan, tempat, status) VALUES (?,?,?,?)")
                ->execute([$nama, $tgl, $tempat, $status]);
            $_SESSION['success_msg'] = "Kegiatan berhasil ditambahkan.";
        }
    } elseif ($action === 'update_status') {
        $id     = (int) ($_POST['id_kegiatan'] ?? 0);
        $status = $_POST['status'] ?? '';
        $pdo->prepare("UPDATE tblKegiatan SET status=? WHERE id_kegiatan=?")->execute([$status, $id]);
        $_SESSION['success_msg'] = "Status kegiatan diperbarui.";
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id_kegiatan'] ?? 0);
        $pdo->prepare("DELETE FROM tblKegiatan WHERE id_kegiatan=?")->execute([$id]);
        $_SESSION['success_msg'] = "Kegiatan dihapus.";
    }
    header("Location: kegiatan.php"); exit;
}

// Upcoming vs Past
$upcoming = $pdo->query("SELECT * FROM tblKegiatan WHERE tanggal_pelaksanaan >= NOW() OR status='Akan Dilaksanakan' ORDER BY tanggal_pelaksanaan ASC")->fetchAll();
$past     = $pdo->query("SELECT * FROM tblKegiatan WHERE tanggal_pelaksanaan < NOW() AND status != 'Akan Dilaksanakan' ORDER BY tanggal_pelaksanaan DESC LIMIT 20")->fetchAll();

$status_badge = ['Akan Dilaksanakan'=>'primary','Selesai'=>'success','Dibatalkan'=>'danger'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i>Kegiatan Gereja</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus me-2"></i>Tambah Kegiatan
    </button>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#upcoming">
        <i class="fas fa-calendar-day me-1"></i>Akan Datang <span class="badge bg-primary ms-1"><?= count($upcoming) ?></span>
    </button></li>
    <li class="nav-item"><button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#past">
        <i class="fas fa-history me-1"></i>Lampau
    </button></li>
</ul>

<div class="tab-content">
<!-- Upcoming -->
<div class="tab-pane fade show active" id="upcoming">
    <?php if (empty($upcoming)): ?>
    <div class="card-admin text-center py-5 text-muted"><i class="fas fa-calendar-times fa-3x mb-3 d-block opacity-25"></i>Belum ada kegiatan yang akan datang.</div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($upcoming as $kg): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card-admin h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-<?= $status_badge[$kg['status']] ?? 'secondary' ?>"><?= $kg['status'] ?></span>
                        <div class="d-flex gap-1">
                            <form method="POST" class="d-inline">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id_kegiatan" value="<?= $kg['id_kegiatan'] ?>">
                                <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                                    <?php foreach(['Akan Dilaksanakan','Selesai','Dibatalkan'] as $s): ?>
                                    <option <?= $kg['status']===$s?'selected':'' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Hapus kegiatan ini?')">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_kegiatan" value="<?= $kg['id_kegiatan'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($kg['nama_kegiatan']) ?></h6>
                    <div class="text-muted small"><i class="fas fa-calendar me-1"></i><?= date('d M Y H:i', strtotime($kg['tanggal_pelaksanaan'])) ?></div>
                    <?php if ($kg['tempat']): ?>
                    <div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($kg['tempat']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Past -->
<div class="tab-pane fade" id="past">
    <div class="card-admin">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Tempat</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (empty($past)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kegiatan lampau.</td></tr>
                    <?php else: foreach ($past as $kg): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($kg['nama_kegiatan']) ?></td>
                        <td class="text-muted small"><?= date('d M Y H:i', strtotime($kg['tanggal_pelaksanaan'])) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($kg['tempat'] ?: '-') ?></td>
                        <td><span class="badge bg-<?= $status_badge[$kg['status']] ?? 'secondary' ?>"><?= $kg['status'] ?></span></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
<div class="modal-dialog"><div class="modal-content border-0 shadow">
<div class="modal-header" style="background:var(--alt-sidebar-bg);color:#fff;">
    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Kegiatan</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
    <input type="hidden" name="action" value="add">
    <div class="mb-3"><label class="form-label fw-semibold">Nama Kegiatan *</label>
        <input type="text" name="nama_kegiatan" class="form-control" required></div>
    <div class="mb-3"><label class="form-label fw-semibold">Tanggal & Waktu *</label>
        <input type="datetime-local" name="tanggal_pelaksanaan" class="form-control" required></div>
    <div class="mb-3"><label class="form-label fw-semibold">Tempat</label>
        <input type="text" name="tempat" class="form-control" placeholder="Gereja / Gedung Serbaguna"></div>
    <div class="mb-3"><label class="form-label fw-semibold">Status Awal</label>
        <select name="status" class="form-select"><option value="Akan Dilaksanakan" selected>Akan Dilaksanakan</option></select></div>
</div>
<div class="modal-footer bg-light"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary-custom" type="submit"><i class="fas fa-save me-1"></i>Simpan</button></div>
</form></div></div></div>

<?php require_once '../../includes/admin_footer.php'; ?>
