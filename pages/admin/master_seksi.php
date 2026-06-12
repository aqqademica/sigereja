<?php
require_once '../../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('master_seksi.php'); // H5
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $nama_seksi = trim($_POST['nama_seksi'] ?? '');
        if ($nama_seksi) {
            $stmt = $pdo->prepare("INSERT INTO tblSeksi (nama_seksi) VALUES (?)");
            $stmt->execute([$nama_seksi]);
            $_SESSION['success_msg'] = "Seksi berhasil ditambahkan.";
        }
    } elseif ($action === 'edit') {
        $id_seksi = $_POST['id_seksi'] ?? '';
        $nama_seksi = trim($_POST['nama_seksi'] ?? '');
        if ($id_seksi && $nama_seksi) {
            $stmt = $pdo->prepare("UPDATE tblSeksi SET nama_seksi = ? WHERE id_seksi = ?");
            $stmt->execute([$nama_seksi, $id_seksi]);
            $_SESSION['success_msg'] = "Seksi berhasil diubah.";
        }
    } elseif ($action === 'delete') {
        $id_seksi = $_POST['id_seksi'] ?? '';
        if ($id_seksi) {
            $stmt = $pdo->prepare("DELETE FROM tblSeksi WHERE id_seksi = ?");
            $stmt->execute([$id_seksi]);
            $_SESSION['success_msg'] = "Seksi berhasil dihapus.";
        }
    }
    header("Location: master_seksi.php");
    exit;
}

$seksis = $pdo->query("SELECT * FROM tblSeksi ORDER BY nama_seksi")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Master Seksi</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Tambah Seksi</button>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="glass-card-admin">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th width="10%">ID</th>
                    <th>Nama Seksi</th>
                    <th width="20%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($seksis)): ?>
                    <tr><td colspan="3" class="text-center text-muted">Belum ada data seksi.</td></tr>
                <?php endif; ?>
                <?php foreach ($seksis as $seksi): ?>
                <tr>
                    <td><?= $seksi['id_seksi'] ?></td>
                    <td><?= htmlspecialchars($seksi['nama_seksi']) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $seksi['id_seksi'] ?>"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $seksi['id_seksi'] ?>"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $seksi['id_seksi'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title">Edit Seksi</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id_seksi" value="<?= $seksi['id_seksi'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Seksi</label>
                                        <input type="text" name="nama_seksi" class="form-control" value="<?= htmlspecialchars($seksi['nama_seksi']) ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal<?= $seksi['id_seksi'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Hapus Seksi</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_seksi" value="<?= $seksi['id_seksi'] ?>">
                                    <p>Yakin ingin menghapus <strong><?= htmlspecialchars($seksi['nama_seksi']) ?></strong>?</p>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Tambah Seksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Nama Seksi</label>
                        <input type="text" name="nama_seksi" class="form-control" required placeholder="Contoh: Pemuda">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/admin_footer.php'; ?>
