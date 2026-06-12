<?php
require_once '../../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('master_jabatan.php'); // H5
    $action = $_POST['action'] ?? '';
    $type = $_POST['type'] ?? ''; // 'majelis' or 'seksi'
    
    if ($action === 'add') {
        $nama = trim($_POST['nama_jabatan'] ?? '');
        if ($nama) {
            $table = ($type === 'majelis') ? 'tblJabatanMajelis' : 'tblJabatanSeksi';
            $stmt = $pdo->prepare("INSERT INTO $table (nama_jabatan) VALUES (?)");
            $stmt->execute([$nama]);
            $_SESSION['success_msg'] = "Jabatan $type berhasil ditambahkan.";
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id_jabatan'] ?? '';
        $nama = trim($_POST['nama_jabatan'] ?? '');
        if ($id && $nama) {
            $table = ($type === 'majelis') ? 'tblJabatanMajelis' : 'tblJabatanSeksi';
            $stmt = $pdo->prepare("UPDATE $table SET nama_jabatan = ? WHERE id_jabatan = ?");
            $stmt->execute([$nama, $id]);
            $_SESSION['success_msg'] = "Jabatan $type berhasil diubah.";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id_jabatan'] ?? '';
        if ($id) {
            $table = ($type === 'majelis') ? 'tblJabatanMajelis' : 'tblJabatanSeksi';
            $stmt = $pdo->prepare("DELETE FROM $table WHERE id_jabatan = ?");
            $stmt->execute([$id]);
            $_SESSION['success_msg'] = "Jabatan $type berhasil dihapus.";
        }
    }
    header("Location: master_jabatan.php");
    exit;
}

$jabatanMajelis = $pdo->query("SELECT * FROM tblJabatanMajelis ORDER BY nama_jabatan")->fetchAll();
$jabatanSeksi = $pdo->query("SELECT * FROM tblJabatanSeksi ORDER BY nama_jabatan")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Master Jabatan</h2>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="glass-card-admin">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0">Jabatan Majelis</h5>
                <button class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addMajelisModal"><i class="fas fa-plus"></i></button>
            </div>
            <table class="table table-hover align-middle">
                <tbody>
                    <?php if (empty($jabatanMajelis)): ?><tr><td class="text-center text-muted">Belum ada data.</td></tr><?php endif; ?>
                    <?php foreach ($jabatanMajelis as $jab): ?>
                    <tr>
                        <td><?= htmlspecialchars($jab['nama_jabatan']) ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm text-primary" data-bs-toggle="modal" data-bs-target="#editMajelisModal<?= $jab['id_jabatan'] ?>"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm text-danger" data-bs-toggle="modal" data-bs-target="#deleteMajelisModal<?= $jab['id_jabatan'] ?>"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <!-- Modal Edit & Delete omitted for brevity in this snippet but would be identical to previous, just setting type=majelis -->
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editMajelisModal<?= $jab['id_jabatan'] ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="edit"><input type="hidden" name="type" value="majelis"><input type="hidden" name="id_jabatan" value="<?= $jab['id_jabatan'] ?>"><input type="text" name="nama_jabatan" class="form-control" value="<?= htmlspecialchars($jab['nama_jabatan']) ?>" required></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div>
                    </div>
                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteMajelisModal<?= $jab['id_jabatan'] ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Hapus</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                        <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="delete"><input type="hidden" name="type" value="majelis"><input type="hidden" name="id_jabatan" value="<?= $jab['id_jabatan'] ?>"><p>Hapus <?= htmlspecialchars($jab['nama_jabatan']) ?>?</p></div><div class="modal-footer"><button type="submit" class="btn btn-danger">Hapus</button></div></form></div></div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="glass-card-admin">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0">Jabatan Seksi</h5>
                <button class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addSeksiModal"><i class="fas fa-plus"></i></button>
            </div>
            <table class="table table-hover align-middle">
                <tbody>
                    <?php if (empty($jabatanSeksi)): ?><tr><td class="text-center text-muted">Belum ada data.</td></tr><?php endif; ?>
                    <?php foreach ($jabatanSeksi as $jab): ?>
                    <tr>
                        <td><?= htmlspecialchars($jab['nama_jabatan']) ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm text-primary" data-bs-toggle="modal" data-bs-target="#editSeksiModal<?= $jab['id_jabatan'] ?>"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm text-danger" data-bs-toggle="modal" data-bs-target="#deleteSeksiModal<?= $jab['id_jabatan'] ?>"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editSeksiModal<?= $jab['id_jabatan'] ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="edit"><input type="hidden" name="type" value="seksi"><input type="hidden" name="id_jabatan" value="<?= $jab['id_jabatan'] ?>"><input type="text" name="nama_jabatan" class="form-control" value="<?= htmlspecialchars($jab['nama_jabatan']) ?>" required></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div>
                    </div>
                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteSeksiModal<?= $jab['id_jabatan'] ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Hapus</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                        <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="delete"><input type="hidden" name="type" value="seksi"><input type="hidden" name="id_jabatan" value="<?= $jab['id_jabatan'] ?>"><p>Hapus <?= htmlspecialchars($jab['nama_jabatan']) ?>?</p></div><div class="modal-footer"><button type="submit" class="btn btn-danger">Hapus</button></div></form></div></div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Majelis Modal -->
<div class="modal fade" id="addMajelisModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Jabatan Majelis</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="add"><input type="hidden" name="type" value="majelis"><input type="text" name="nama_jabatan" class="form-control" required placeholder="Contoh: Sintua"></div><div class="modal-footer"><button type="submit" class="btn btn-primary-custom">Simpan</button></div></form></div></div>
</div>

<!-- Add Seksi Modal -->
<div class="modal fade" id="addSeksiModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Jabatan Seksi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="add"><input type="hidden" name="type" value="seksi"><input type="text" name="nama_jabatan" class="form-control" required placeholder="Contoh: Ketua Seksi"></div><div class="modal-footer"><button type="submit" class="btn btn-primary-custom">Simpan</button></div></form></div></div>
</div>

<?php require_once '../../includes/admin_footer.php'; ?>
