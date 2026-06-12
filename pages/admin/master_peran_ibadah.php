<?php
require_once '../../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('master_peran_ibadah.php'); // H5
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $nama_peran = trim($_POST['nama_peran'] ?? '');
        if ($nama_peran) {
            $stmt = $pdo->prepare("INSERT INTO tblPeranIbadah (nama_peran) VALUES (?)");
            $stmt->execute([$nama_peran]);
            $_SESSION['success_msg'] = "Peran Ibadah berhasil ditambahkan.";
        }
    } elseif ($action === 'edit') {
        $id_peran = $_POST['id_peran'] ?? '';
        $nama_peran = trim($_POST['nama_peran'] ?? '');
        if ($id_peran && $nama_peran) {
            $stmt = $pdo->prepare("UPDATE tblPeranIbadah SET nama_peran = ? WHERE id_peran_ibadah = ?");
            $stmt->execute([$nama_peran, $id_peran]);
            $_SESSION['success_msg'] = "Peran Ibadah berhasil diubah.";
        }
    } elseif ($action === 'delete') {
        $id_peran = $_POST['id_peran'] ?? '';
        if ($id_peran) {
            $stmt = $pdo->prepare("DELETE FROM tblPeranIbadah WHERE id_peran_ibadah = ?");
            $stmt->execute([$id_peran]);
            $_SESSION['success_msg'] = "Peran Ibadah berhasil dihapus.";
        }
    }
    header("Location: master_peran_ibadah.php");
    exit;
}

$perans = $pdo->query("SELECT * FROM tblPeranIbadah ORDER BY nama_peran")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Master Peran Ibadah</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Tambah Peran</button>
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
                    <th>Nama Peran Ibadah (Petugas)</th>
                    <th width="20%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($perans)): ?>
                    <tr><td colspan="3" class="text-center text-muted">Belum ada data peran ibadah.</td></tr>
                <?php endif; ?>
                <?php foreach ($perans as $peran): ?>
                <tr>
                    <td><?= $peran['id_peran_ibadah'] ?></td>
                    <td><?= htmlspecialchars($peran['nama_peran']) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $peran['id_peran_ibadah'] ?>"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $peran['id_peran_ibadah'] ?>"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $peran['id_peran_ibadah'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title">Edit Peran Ibadah</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id_peran" value="<?= $peran['id_peran_ibadah'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Peran</label>
                                        <input type="text" name="nama_peran" class="form-control" value="<?= htmlspecialchars($peran['nama_peran']) ?>" required>
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
                <div class="modal fade" id="deleteModal<?= $peran['id_peran_ibadah'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Hapus Peran Ibadah</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_peran" value="<?= $peran['id_peran_ibadah'] ?>">
                                    <p>Yakin ingin menghapus <strong><?= htmlspecialchars($peran['nama_peran']) ?></strong>?</p>
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
                <h5 class="modal-title">Tambah Peran Ibadah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Nama Peran</label>
                        <input type="text" name="nama_peran" class="form-control" required placeholder="Contoh: Pemimpin Pujian">
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
