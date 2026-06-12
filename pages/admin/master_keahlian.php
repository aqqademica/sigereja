<?php
require_once '../../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('master_keahlian.php'); // H5
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $nama_keahlian = trim($_POST['nama_keahlian'] ?? '');
        if ($nama_keahlian) {
            $stmt = $pdo->prepare("INSERT INTO tblKeahlian (nama_keahlian) VALUES (?)");
            $stmt->execute([$nama_keahlian]);
            $_SESSION['success_msg'] = "Keahlian berhasil ditambahkan.";
        }
    } elseif ($action === 'edit') {
        $id_keahlian = $_POST['id_keahlian'] ?? '';
        $nama_keahlian = trim($_POST['nama_keahlian'] ?? '');
        if ($id_keahlian && $nama_keahlian) {
            $stmt = $pdo->prepare("UPDATE tblKeahlian SET nama_keahlian = ? WHERE id_keahlian = ?");
            $stmt->execute([$nama_keahlian, $id_keahlian]);
            $_SESSION['success_msg'] = "Keahlian berhasil diubah.";
        }
    } elseif ($action === 'delete') {
        $id_keahlian = $_POST['id_keahlian'] ?? '';
        if ($id_keahlian) {
            $stmt = $pdo->prepare("DELETE FROM tblKeahlian WHERE id_keahlian = ?");
            $stmt->execute([$id_keahlian]);
            $_SESSION['success_msg'] = "Keahlian berhasil dihapus.";
        }
    }
    header("Location: master_keahlian.php");
    exit;
}

$keahlians = $pdo->query("SELECT * FROM tblKeahlian ORDER BY nama_keahlian")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Master Keahlian</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Tambah Keahlian</button>
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
                    <th>Nama Keahlian</th>
                    <th width="20%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($keahlians)): ?>
                    <tr><td colspan="3" class="text-center text-muted">Belum ada data keahlian.</td></tr>
                <?php endif; ?>
                <?php foreach ($keahlians as $keahlian): ?>
                <tr>
                    <td><?= $keahlian['id_keahlian'] ?></td>
                    <td><?= htmlspecialchars($keahlian['nama_keahlian']) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $keahlian['id_keahlian'] ?>"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $keahlian['id_keahlian'] ?>"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $keahlian['id_keahlian'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title">Edit Keahlian</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id_keahlian" value="<?= $keahlian['id_keahlian'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Keahlian</label>
                                        <input type="text" name="nama_keahlian" class="form-control" value="<?= htmlspecialchars($keahlian['nama_keahlian']) ?>" required>
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
                <div class="modal fade" id="deleteModal<?= $keahlian['id_keahlian'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Hapus Keahlian</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_keahlian" value="<?= $keahlian['id_keahlian'] ?>">
                                    <p>Yakin ingin menghapus <strong><?= htmlspecialchars($keahlian['nama_keahlian']) ?></strong>?</p>
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
                <h5 class="modal-title">Tambah Keahlian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Nama Keahlian</label>
                        <input type="text" name="nama_keahlian" class="form-control" required placeholder="Contoh: Pemain Musik">
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
