<?php
require_once '../../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('jadwal.php'); // H5
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $nama         = trim($_POST['nama_ibadah'] ?? '');
        $tanggal      = $_POST['tanggal'] ?? '';
        $waktu        = $_POST['waktu'] ?? '';
        $id_sektor    = !empty($_POST['id_sektor']) ? (int)$_POST['id_sektor'] : NULL;

        if ($nama && $tanggal && $waktu) {
            $tanggal_waktu = $tanggal . ' ' . $waktu . ':00';
            $pdo->prepare("INSERT INTO tblJadwalIbadah (nama_ibadah, tanggal_waktu, id_sektor) VALUES (?, ?, ?)")
                ->execute([$nama, $tanggal_waktu, $id_sektor]);
            $_SESSION['success_msg'] = "Jadwal Ibadah berhasil ditambahkan.";
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id_ibadah'] ?? 0);
        if ($id) {
            $pdo->prepare("DELETE FROM tblJadwalIbadah WHERE id_ibadah = ?")->execute([$id]);
            $_SESSION['success_msg'] = "Jadwal dihapus.";
        }
    }
    header("Location: jadwal.php");
    exit;
}

$sektors = $pdo->query("SELECT * FROM tblSektor ORDER BY nama_sektor")->fetchAll();
$jadwal  = $pdo->query("SELECT j.*, s.nama_sektor FROM tblJadwalIbadah j LEFT JOIN tblSektor s ON j.id_sektor = s.id_sektor ORDER BY j.tanggal_waktu DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="fas fa-church me-2 text-primary"></i>Manajemen Jadwal Ibadah</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus me-2"></i>Buat Jadwal Baru
    </button>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="glass-card-admin">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal &amp; Waktu</th>
                    <th>Nama Ibadah</th>
                    <th>Sektor</th>
                    <th class="text-center" width="22%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jadwal)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada jadwal ibadah.</td></tr>
                <?php endif; ?>
                <?php foreach ($jadwal as $j): ?>
                <tr>
                    <td class="fw-bold text-primary">
                        <?= date('d M Y', strtotime($j['tanggal_waktu'])) ?><br>
                        <small class="text-muted"><i class="fas fa-clock"></i> <?= date('H:i', strtotime($j['tanggal_waktu'])) ?> WIB</small>
                    </td>
                    <td class="fw-bold"><?= htmlspecialchars($j['nama_ibadah']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($j['nama_sektor'] ?? '-') ?></span></td>
                    <td class="text-center">
                        <a href="jadwal_detail.php?id=<?= $j['id_ibadah'] ?>" class="btn btn-sm btn-outline-primary mb-1 w-100">
                            <i class="fas fa-tasks"></i> Kelola Liturgi &amp; Petugas
                        </a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus jadwal ini beserta seluruh data liturgi dan petugasnya?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id_ibadah" value="<?= $j['id_ibadah'] ?>">
                            <button class="btn btn-sm btn-outline-danger w-100"><i class="fas fa-trash"></i> Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:var(--alt-sidebar-bg);color:#fff;">
                <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Buat Jadwal Ibadah</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Ibadah <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ibadah" class="form-control" required autofocus placeholder="Cth: Ibadah Minggu Pagi">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Waktu <span class="text-danger">*</span></label>
                            <input type="time" name="waktu" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sektor <span class="text-muted fw-normal">(Opsional)</span></label>
                        <select name="id_sektor" class="form-select">
                            <option value="">-- Semua Sektor / Umum --</option>
                            <?php foreach ($sektors as $s): ?>
                                <option value="<?= $s['id_sektor'] ?>"><?= htmlspecialchars($s['nama_sektor']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/admin_footer.php'; ?>
