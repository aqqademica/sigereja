<?php
require_once '../../includes/admin_header.php';

// Handle Add / Edit / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('master_sektor.php'); // H5
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $nama_sektor = trim($_POST['nama_sektor'] ?? '');
        if ($nama_sektor) {
            $stmt = $pdo->prepare("INSERT INTO tblSektor (nama_sektor) VALUES (?)");
            $stmt->execute([$nama_sektor]);
            $_SESSION['success_msg'] = "Sektor berhasil ditambahkan.";
        }
    } elseif ($action === 'edit') {
        $id_sektor = $_POST['id_sektor'] ?? '';
        $nama_sektor = trim($_POST['nama_sektor'] ?? '');
        if ($id_sektor && $nama_sektor) {
            $stmt = $pdo->prepare("UPDATE tblSektor SET nama_sektor = ? WHERE id_sektor = ?");
            $stmt->execute([$nama_sektor, $id_sektor]);
            $_SESSION['success_msg'] = "Sektor berhasil diubah.";
        }
    } elseif ($action === 'delete') {
        $id_sektor = $_POST['id_sektor'] ?? '';
        if ($id_sektor) {
            $stmt = $pdo->prepare("DELETE FROM tblSektor WHERE id_sektor = ?");
            $stmt->execute([$id_sektor]);
            $_SESSION['success_msg'] = "Sektor berhasil dihapus.";
        }
    }
    header("Location: master_sektor.php");
    exit;
}

// Fetch Data
$sektors = $pdo->query("SELECT * FROM tblSektor ORDER BY nama_sektor")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Master Sektor</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Tambah Sektor</button>
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
                    <th>Nama Sektor</th>
                    <th width="20%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sektors)): ?>
                    <tr><td colspan="3" class="text-center text-muted">Belum ada data sektor.</td></tr>
                <?php endif; ?>
                <?php foreach ($sektors as $sektor): ?>
                <tr>
                    <td><?= $sektor['id_sektor'] ?></td>
                    <td><?= htmlspecialchars($sektor['nama_sektor']) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $sektor['id_sektor'] ?>"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $sektor['id_sektor'] ?>"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $sektor['id_sektor'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title">Edit Sektor</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id_sektor" value="<?= $sektor['id_sektor'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Sektor</label>
                                        <input type="text" name="nama_sektor" class="form-control" value="<?= htmlspecialchars($sektor['nama_sektor']) ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal<?= $sektor['id_sektor'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Hapus Sektor</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_sektor" value="<?= $sektor['id_sektor'] ?>">
                                    <p>Apakah Anda yakin ingin menghapus sektor <strong><?= htmlspecialchars($sektor['nama_sektor']) ?></strong>?</p>
                                    <p class="text-danger small"><i class="fas fa-exclamation-triangle me-1"></i> Data yang dihapus tidak dapat dikembalikan dan dapat mempengaruhi data jemaat terkait.</p>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
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
                <h5 class="modal-title">Tambah Sektor Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Nama Sektor</label>
                        <input type="text" name="nama_sektor" class="form-control" required placeholder="Contoh: Sektor A">
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
