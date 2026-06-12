<?php
require_once '../../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('keluarga.php'); // H5
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $nomor_kk = trim($_POST['nomor_kk'] ?? '');
        if ($nomor_kk) {
            try {
                $pdo->prepare("INSERT INTO tblKeluarga (nomor_kk) VALUES (?)")->execute([$nomor_kk]);
                $_SESSION['success_msg'] = "Keluarga berhasil ditambahkan.";
            } catch (PDOException $e) {
                $_SESSION['error_msg'] = "Nomor KK sudah terdaftar.";
            }
        }
    } elseif ($action === 'delete') {
        $id_keluarga = $_POST['id_keluarga'] ?? '';
        if ($id_keluarga) {
            $pdo->prepare("DELETE FROM tblKeluarga WHERE id_keluarga = ?")->execute([$id_keluarga]);
            $_SESSION['success_msg'] = "Keluarga berhasil dihapus.";
        }
    }
    header("Location: keluarga.php"); exit;
}

$keluargas = $pdo->query("
    SELECT k.*, j.nama_lengkap AS kepala_keluarga,
           (SELECT COUNT(*) FROM tblJemaat WHERE id_keluarga = k.id_keluarga) AS jumlah_anggota
    FROM tblKeluarga k
    LEFT JOIN tblJemaat j ON k.id_kepala_keluarga = j.id_jemaat
    ORDER BY k.nomor_kk
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="fas fa-home me-2 text-primary"></i>Manajemen Keluarga</h2>
    <div class="d-flex gap-2">
        <a href="keluarga_add.php" class="btn btn-primary-custom">
            <i class="fas fa-plus me-2"></i>Daftarkan Keluarga Baru
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_msg'])): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-widget">
            <div class="stat-icon blue"><i class="fas fa-home"></i></div>
            <div><div class="stat-value"><?= count($keluargas) ?></div><div class="stat-label">Total Keluarga (KK)</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-widget">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div><div class="stat-value"><?= array_sum(array_column($keluargas, 'jumlah_anggota')) ?></div><div class="stat-label">Total Anggota</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-widget">
            <div class="stat-icon orange"><i class="fas fa-user-slash"></i></div>
            <div><div class="stat-value"><?= count(array_filter($keluargas, fn($k) => !$k['id_kepala_keluarga'])) ?></div><div class="stat-label">Tanpa Kepala KK</div></div>
        </div>
    </div>
</div>

<div class="card-admin">
    <div class="card-header">
        <h5><i class="fas fa-list me-2"></i>Daftar Keluarga</h5>
        <input type="search" id="searchKK" class="form-control form-control-sm" placeholder="Cari No. KK / Kepala KK..." style="width:220px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="kkTable">
                <thead>
                    <tr>
                        <th>Nomor KK</th>
                        <th>Kepala Keluarga</th>
                        <th class="text-center">Jumlah Anggota</th>
                        <th class="text-center" width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($keluargas)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data keluarga. <a href="keluarga_add.php" class="fw-bold">Daftarkan sekarang.</a></td></tr>
                    <?php endif; ?>
                    <?php foreach ($keluargas as $k): ?>
                    <tr>
                        <td><span class="badge bg-secondary font-monospace fs-6"><?= htmlspecialchars($k['nomor_kk']) ?></span></td>
                        <td>
                            <?php if ($k['kepala_keluarga']): ?>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:30px;height:30px;background:hsl(246,80%,60%);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">
                                    <?= strtoupper(substr($k['kepala_keluarga'],0,1)) ?>
                                </div>
                                <?= htmlspecialchars($k['kepala_keluarga']) ?>
                            </div>
                            <?php else: ?>
                            <span class="text-muted fst-italic small"><i class="fas fa-exclamation-triangle text-warning me-1"></i>Belum diatur</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><span class="badge bg-primary rounded-pill"><?= $k['jumlah_anggota'] ?> Orang</span></td>
                        <td class="text-center">
                            <a href="keluarga_detail.php?id=<?= $k['id_keluarga'] ?>" class="btn btn-sm btn-outline-info me-1"><i class="fas fa-eye me-1"></i>Detail</a>
                            <button class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal" data-bs-target="#deleteModal<?= $k['id_keluarga'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal<?= $k['id_keluarga'] ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content border-0 shadow">
                            <div class="modal-header bg-danger text-white"><h5 class="modal-title">Hapus Keluarga</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_keluarga" value="<?= $k['id_keluarga'] ?>">
                                <p>Hapus KK <strong><?= htmlspecialchars($k['nomor_kk']) ?></strong>?</p>
                                <div class="alert alert-warning small">Data jemaat anggota tidak akan dihapus, tetapi relasi keluarganya akan direset.</div>
                            </div>
                            <div class="modal-footer bg-light"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger" type="submit">Hapus</button></div>
                            </form>
                        </div></div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('searchKK').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#kkTable tbody tr:not(.modal)').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

<?php require_once '../../includes/admin_footer.php'; ?>
