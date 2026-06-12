<?php
require_once '../../includes/admin_header.php';

// Only Super Admin & Admin Sistem
if (!in_array($_SESSION['role'], ['Super Admin', 'Admin Sistem'])) {
    echo '<div class="alert alert-danger">Akses ditolak.</div>';
    require_once '../../includes/admin_footer.php';
    exit;
}

$error_msg = ''; $success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('users.php'); // H5
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $role     = $_POST['role'] ?? 'Jemaat';
        $status   = $_POST['status_verifikasi'] ?? 'Approved';
        $password = $_POST['password'] ?? '';
        if ($username && $email && $password) {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare("INSERT INTO users (username, email, password, role, status_verifikasi) VALUES (?,?,?,?,?)")
                    ->execute([$username, $email, $hash, $role, $status]);
                $_SESSION['success_msg'] = "Akun '{$username}' berhasil dibuat.";
            } catch (PDOException $e) {
                $_SESSION['error_msg'] = "Username atau email sudah terdaftar.";
            }
        } else {
            $_SESSION['error_msg'] = "Semua field wajib diisi.";
        }
    }

    if ($action === 'update') {
        $id     = (int) ($_POST['id'] ?? 0);
        $role   = $_POST['role'] ?? '';
        $status = $_POST['status_verifikasi'] ?? '';
        $email  = trim($_POST['email'] ?? '');
        $pdo->prepare("UPDATE users SET role=?, status_verifikasi=?, email=? WHERE id=?")
            ->execute([$role, $status, $email, $id]);
        if (!empty($_POST['new_password'])) {
            $hash = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $id]);
        }
        $_SESSION['success_msg'] = "Akun berhasil diperbarui.";
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id && $id !== (int) $_SESSION['user_id']) {
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            $_SESSION['success_msg'] = "Akun berhasil dihapus.";
        } else {
            $_SESSION['error_msg'] = "Tidak dapat menghapus akun Anda sendiri.";
        }
    }

    header("Location: users.php"); exit;
}

$limit = 50;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_pages = ceil($total_users / $limit);

$users = $pdo->query("SELECT * FROM users ORDER BY role, username LIMIT $limit OFFSET $offset")->fetchAll();
$roles  = ['Super Admin','Admin Sistem','Sekretaris','Ketua Sektor','Pendeta','Jemaat'];
$status_opts = ['Pending','Verified Sektor','Approved Majelis'];

$role_badges = [
    'Super Admin'    => 'danger',
    'Admin Sistem'   => 'dark',
    'Sekretaris'     => 'primary',
    'Ketua Sektor'   => 'info',
    'Pendeta'        => 'success',
    'Jemaat'         => 'secondary',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="fas fa-user-shield me-2 text-primary"></i>Manajemen Akun</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="fas fa-plus me-2"></i>Buat Akun Baru
    </button>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_msg'])): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-widget">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div><div class="stat-value"><?= count($users) ?></div><div class="stat-label">Total Akun</div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-widget">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div><div class="stat-value"><?= count(array_filter($users, fn($u) => $u['status_verifikasi'] === 'Pending')) ?></div><div class="stat-label">Pending Verifikasi</div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-widget">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div><div class="stat-value"><?= count(array_filter($users, fn($u) => $u['status_verifikasi'] === 'Approved Majelis')) ?></div><div class="stat-label">Approved</div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-widget">
            <div class="stat-icon purple"><i class="fas fa-user-tie"></i></div>
            <div><div class="stat-value"><?= count(array_filter($users, fn($u) => !in_array($u['role'], ['Jemaat']))) ?></div><div class="stat-label">Pengurus</div></div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card-admin">
    <div class="card-header">
        <h5><i class="fas fa-list me-2"></i>Daftar Akun Pengguna</h5>
        <input type="search" class="form-control form-control-sm w-auto" id="searchUser" placeholder="Cari username / email..." style="width:220px!important;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="userTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status Verifikasi</th>
                        <th>Dibuat</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $i => $u): ?>
                <tr>
                    <td class="text-muted small"><?= $offset + $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;background:hsl(246,80%,60%);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.82rem;flex-shrink:0;">
                                <?= strtoupper(substr($u['username'], 0, 1)) ?>
                            </div>
                            <span class="fw-semibold"><?= htmlspecialchars($u['username']) ?></span>
                            <?php if ($u['id'] == $_SESSION['user_id']): ?><span class="badge bg-primary ms-1" style="font-size:.62rem;">Anda</span><?php endif; ?>
                        </div>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge bg-<?= $role_badges[$u['role']] ?? 'secondary' ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                    <td>
                        <?php
                        $sv = $u['status_verifikasi'];
                        $sv_color = $sv === 'Approved Majelis' ? 'success' : ($sv === 'Pending' ? 'warning text-dark' : 'info');
                        ?>
                        <span class="badge bg-<?= $sv_color ?>"><?= htmlspecialchars($sv) ?></span>
                    </td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary me-1"
                            data-bs-toggle="modal" data-bs-target="#editModal"
                            data-id="<?= $u['id'] ?>"
                            data-username="<?= htmlspecialchars($u['username']) ?>"
                            data-email="<?= htmlspecialchars($u['email']) ?>"
                            data-role="<?= htmlspecialchars($u['role']) ?>"
                            data-status="<?= htmlspecialchars($u['status_verifikasi']) ?>"
                            title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <button class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                            data-id="<?= $u['id'] ?>"
                            data-name="<?= htmlspecialchars($u['username']) ?>"
                            title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4 pb-2">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">Sebelumnya</a>
                </li>
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Selanjutnya</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
<div class="modal-dialog"><div class="modal-content border-0 shadow">
<div class="modal-header" style="background:var(--alt-sidebar-bg);color:#fff;">
    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Buat Akun Baru</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
    <input type="hidden" name="action" value="create">
    <div class="mb-3"><label class="form-label fw-semibold">Username *</label>
        <input type="text" name="username" class="form-control" required></div>
    <div class="mb-3"><label class="form-label fw-semibold">Email *</label>
        <input type="email" name="email" class="form-control" required></div>
    <div class="mb-3"><label class="form-label fw-semibold">Password *</label>
        <input type="password" name="password" class="form-control" required minlength="8"></div>
    <div class="row">
        <div class="col-6 mb-3"><label class="form-label fw-semibold">Role</label>
            <select name="role" class="form-select"><?php foreach($roles as $r): ?><option value="<?= $r ?>"><?= $r ?></option><?php endforeach; ?></select></div>
        <div class="col-6 mb-3"><label class="form-label fw-semibold">Status</label>
            <select name="status_verifikasi" class="form-select"><option value="Approved Majelis">Approved</option><option value="Pending">Pending</option></select></div>
    </div>
</div>
<div class="modal-footer bg-light"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary-custom" type="submit">Buat Akun</button></div>
</form></div></div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog"><div class="modal-content border-0 shadow">
<div class="modal-header" style="background:var(--alt-primary);color:#fff;">
    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Akun: <span id="editUsername"></span></h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" id="editId">
    <div class="mb-3"><label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" id="editEmail" class="form-control" required></div>
    <div class="row">
        <div class="col-6 mb-3"><label class="form-label fw-semibold">Role</label>
            <select name="role" id="editRole" class="form-select"><?php foreach($roles as $r): ?><option value="<?= $r ?>"><?= $r ?></option><?php endforeach; ?></select></div>
        <div class="col-6 mb-3"><label class="form-label fw-semibold">Status Verifikasi</label>
            <select name="status_verifikasi" id="editStatus" class="form-select">
                <option value="Pending">Pending</option>
                <option value="Verified Sektor">Verified Sektor</option>
                <option value="Approved Majelis">Approved Majelis</option>
            </select></div>
    </div>
    <div class="mb-2"><label class="form-label fw-semibold">Reset Password (kosongkan jika tidak ingin diubah)</label>
        <input type="password" name="new_password" class="form-control" minlength="8" placeholder="Password baru (min 8 karakter)"></div>
</div>
<div class="modal-footer bg-light"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary-custom" type="submit">Simpan Perubahan</button></div>
</form></div></div></div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
<div class="modal-dialog"><div class="modal-content border-0 shadow">
<div class="modal-header bg-danger text-white">
    <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Hapus Akun</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
    <p>Yakin ingin menghapus akun <strong id="deleteName"></strong>?</p>
    <div class="alert alert-warning small"><i class="fas fa-exclamation-triangle me-1"></i>Aksi ini tidak dapat dibatalkan. Data terkait akan terputus dari akun ini.</div>
</div>
<div class="modal-footer bg-light"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger" type="submit">Hapus Akun</button></div>
</form></div></div></div>

<script>
// Edit modal pre-fill
document.getElementById('editModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editId').value      = btn.dataset.id;
    document.getElementById('editUsername').textContent = btn.dataset.username;
    document.getElementById('editEmail').value   = btn.dataset.email;
    document.getElementById('editRole').value    = btn.dataset.role;
    document.getElementById('editStatus').value  = btn.dataset.status;
});
// Delete modal pre-fill
document.getElementById('deleteModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('deleteId').value   = btn.dataset.id;
    document.getElementById('deleteName').textContent = btn.dataset.name;
});
// Search
document.getElementById('searchUser').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#userTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

<?php require_once '../../includes/admin_footer.php'; ?>
