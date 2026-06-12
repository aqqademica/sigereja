<?php
require_once '../../includes/admin_header.php';

// Cek akses verifikasi
$is_sekretaris_or_admin = in_array($_SESSION['role'], ['Super Admin', 'Admin Sistem', 'Sekretaris']);
$is_pendeta = in_array($_SESSION['role'], ['Super Admin', 'Pendeta']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('pengurus.php'); // H5
    $action = $_POST['action'] ?? '';
    $type = $_POST['type'] ?? '';
    
    // Approval
    if ($action === 'approve') {
        $id = $_POST['id'] ?? '';
        if ($id && $type) {
            if ($type === 'sektor' && $is_sekretaris_or_admin) {
                $pdo->prepare("UPDATE tblPengurusSektor SET status_approval = 'Approved', diverifikasi_oleh = ? WHERE id_pengurus = ?")->execute([$_SESSION['user_id'], $id]);
            } elseif ($type === 'seksi' && $is_sekretaris_or_admin) {
                $pdo->prepare("UPDATE tblPengurusSeksi SET status_approval = 'Approved', diverifikasi_oleh = ? WHERE id_pengurus = ?")->execute([$_SESSION['user_id'], $id]);
            } elseif ($type === 'majelis' && $is_pendeta) {
                $pdo->prepare("UPDATE tblDataMajelis SET status_verifikasi_pendeta = 'Approved' WHERE id_data_majelis = ?")->execute([$id]);
            }
            $_SESSION['success_msg'] = "Jabatan berhasil disetujui.";
        }
    } 
    // Tambah Pengurus
    elseif ($action === 'add') {
        $id_jemaat = $_POST['id_jemaat'] ?? '';
        $periode = $_POST['periode_tahun'] ?? '';
        
        if ($id_jemaat) {
            if ($type === 'sektor') {
                $id_sektor = $_POST['id_sektor'] ?? '';
                $jabatan = $_POST['jabatan'] ?? '';
                $pdo->prepare("INSERT INTO tblPengurusSektor (id_jemaat, id_sektor, jabatan, periode_tahun) VALUES (?, ?, ?, ?)")
                    ->execute([$id_jemaat, $id_sektor, $jabatan, $periode]);
            } elseif ($type === 'seksi') {
                $id_seksi = $_POST['id_seksi'] ?? '';
                $id_jabatan = $_POST['id_jabatan'] ?? '';
                $pdo->prepare("INSERT INTO tblPengurusSeksi (id_jemaat, id_seksi, id_jabatan, periode_tahun) VALUES (?, ?, ?, ?)")
                    ->execute([$id_jemaat, $id_seksi, $id_jabatan, $periode]);
            } elseif ($type === 'majelis') {
                $id_jabatan = $_POST['id_jabatan'] ?? '';
                $pdo->prepare("INSERT INTO tblDataMajelis (id_jemaat, id_jabatan_majelis, periode_tahun) VALUES (?, ?, ?)")
                    ->execute([$id_jemaat, $id_jabatan, $periode]);
            }
            $_SESSION['success_msg'] = "Pengurus berhasil ditambahkan (Pending Approval).";
        }
    }
    header("Location: pengurus.php");
    exit;
}

// Fetch Data (using Left Join to get names)
$pengurus_sektor = $pdo->query("SELECT p.*, j.nama_lengkap, s.nama_sektor, u.username as verifikator FROM tblPengurusSektor p JOIN tblJemaat j ON p.id_jemaat = j.id_jemaat JOIN tblSektor s ON p.id_sektor = s.id_sektor LEFT JOIN users u ON p.diverifikasi_oleh = u.id ORDER BY p.status_approval DESC")->fetchAll();
$pengurus_seksi = $pdo->query("SELECT p.*, j.nama_lengkap, s.nama_seksi, jab.nama_jabatan, u.username as verifikator FROM tblPengurusSeksi p JOIN tblJemaat j ON p.id_jemaat = j.id_jemaat JOIN tblSeksi s ON p.id_seksi = s.id_seksi JOIN tblJabatanSeksi jab ON p.id_jabatan = jab.id_jabatan LEFT JOIN users u ON p.diverifikasi_oleh = u.id ORDER BY p.status_approval DESC")->fetchAll();
$majelis = $pdo->query("SELECT m.*, j.nama_lengkap, jab.nama_jabatan FROM tblDataMajelis m JOIN tblJemaat j ON m.id_jemaat = j.id_jemaat JOIN tblJabatanMajelis jab ON m.id_jabatan_majelis = jab.id_jabatan ORDER BY m.status_verifikasi_pendeta DESC")->fetchAll();

$list_jemaat = $pdo->query("SELECT id_jemaat, nama_lengkap FROM tblJemaat WHERE status_keanggotaan = 'Aktif' ORDER BY nama_lengkap")->fetchAll();
$list_sektor = $pdo->query("SELECT * FROM tblSektor")->fetchAll();
$list_seksi = $pdo->query("SELECT * FROM tblSeksi")->fetchAll();
$list_jab_seksi = $pdo->query("SELECT * FROM tblJabatanSeksi")->fetchAll();
$list_jab_majelis = $pdo->query("SELECT * FROM tblJabatanMajelis")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Manajemen Kepengurusan</h2>
    <div>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSektorModal">Tambah Pengurus Sektor</button>
        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addSeksiModal">Tambah Pengurus Seksi</button>
        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#addMajelisModal">Tambah Data Majelis</button>
    </div>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4" id="pengurusTab">
    <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#sektor">Pengurus Sektor</button></li>
    <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#seksi">Pengurus Seksi</button></li>
    <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#majelis">Majelis Jemaat</button></li>
</ul>

<div class="tab-content glass-card-admin p-0 border-0 shadow-none bg-transparent">
    
    <!-- SEKTOR TAB -->
    <div class="tab-pane fade show active glass-card-admin" id="sektor">
        <h5 class="fw-bold mb-3">Pengurus Sektor</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Nama Jemaat</th><th>Sektor</th><th>Jabatan</th><th>Periode</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach($pengurus_sektor as $ps): ?>
                    <tr>
                        <td><?= htmlspecialchars($ps['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($ps['nama_sektor']) ?></td>
                        <td><?= htmlspecialchars($ps['jabatan']) ?></td>
                        <td><?= htmlspecialchars($ps['periode_tahun']) ?></td>
                        <td>
                            <?php if($ps['status_approval'] == 'Approved'): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Disetujui (<?= $ps['verifikator'] ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($ps['status_approval'] == 'Pending' && $is_sekretaris_or_admin): ?>
                            <form method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="approve"><input type="hidden" name="type" value="sektor"><input type="hidden" name="id" value="<?= $ps['id_pengurus'] ?>"><button class="btn btn-sm btn-success">Setujui</button></form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SEKSI TAB -->
    <div class="tab-pane fade glass-card-admin" id="seksi">
        <h5 class="fw-bold mb-3">Pengurus Seksi</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Nama Jemaat</th><th>Seksi</th><th>Jabatan</th><th>Periode</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach($pengurus_seksi as $ps): ?>
                    <tr>
                        <td><?= htmlspecialchars($ps['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($ps['nama_seksi']) ?></td>
                        <td><?= htmlspecialchars($ps['nama_jabatan']) ?></td>
                        <td><?= htmlspecialchars($ps['periode_tahun']) ?></td>
                        <td>
                            <?php if($ps['status_approval'] == 'Approved'): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Disetujui (<?= $ps['verifikator'] ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($ps['status_approval'] == 'Pending' && $is_sekretaris_or_admin): ?>
                            <form method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="approve"><input type="hidden" name="type" value="seksi"><input type="hidden" name="id" value="<?= $ps['id_pengurus'] ?>"><button class="btn btn-sm btn-success">Setujui</button></form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MAJELIS TAB -->
    <div class="tab-pane fade glass-card-admin" id="majelis">
        <h5 class="fw-bold mb-3">Data Majelis Jemaat</h5>
        <div class="alert alert-info py-2"><i class="fas fa-info-circle me-1"></i> Data Majelis hanya dapat diverifikasi oleh Pendeta.</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Nama Jemaat</th><th>Jabatan Majelis</th><th>Periode</th><th>Status Pendeta</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach($majelis as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($m['nama_jabatan']) ?></td>
                        <td><?= htmlspecialchars($m['periode_tahun']) ?></td>
                        <td>
                            <?php if($m['status_verifikasi_pendeta'] == 'Approved'): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Disetujui Pendeta</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Menunggu Pendeta</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($m['status_verifikasi_pendeta'] == 'Pending' && $is_pendeta): ?>
                            <form method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="approve"><input type="hidden" name="type" value="majelis"><input type="hidden" name="id" value="<?= $m['id_data_majelis'] ?>"><button class="btn btn-sm btn-success">Setujui</button></form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Tambah Sektor -->
<div class="modal fade" id="addSektorModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Pengurus Sektor</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="add"><input type="hidden" name="type" value="sektor">
    <div class="mb-3"><label>Jemaat</label><select name="id_jemaat" class="form-select" required><option value="">- Pilih Jemaat -</option><?php foreach($list_jemaat as $j) echo "<option value='{$j['id_jemaat']}'>{$j['nama_lengkap']}</option>"; ?></select></div>
    <div class="mb-3"><label>Sektor</label><select name="id_sektor" class="form-select" required><option value="">- Pilih Sektor -</option><?php foreach($list_sektor as $s) echo "<option value='{$s['id_sektor']}'>{$s['nama_sektor']}</option>"; ?></select></div>
    <div class="mb-3"><label>Jabatan (Teks)</label><input type="text" name="jabatan" class="form-control" required placeholder="Contoh: Ketua"></div>
    <div class="mb-3"><label>Periode</label><input type="text" name="periode_tahun" class="form-control" required placeholder="Contoh: 2024-2026"></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div></div>

<!-- Modal Tambah Seksi -->
<div class="modal fade" id="addSeksiModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Pengurus Seksi</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="add"><input type="hidden" name="type" value="seksi">
    <div class="mb-3"><label>Jemaat</label><select name="id_jemaat" class="form-select" required><option value="">- Pilih Jemaat -</option><?php foreach($list_jemaat as $j) echo "<option value='{$j['id_jemaat']}'>{$j['nama_lengkap']}</option>"; ?></select></div>
    <div class="mb-3"><label>Seksi</label><select name="id_seksi" class="form-select" required><option value="">- Pilih Seksi -</option><?php foreach($list_seksi as $s) echo "<option value='{$s['id_seksi']}'>{$s['nama_seksi']}</option>"; ?></select></div>
    <div class="mb-3"><label>Jabatan</label><select name="id_jabatan" class="form-select" required><option value="">- Pilih Jabatan -</option><?php foreach($list_jab_seksi as $js) echo "<option value='{$js['id_jabatan']}'>{$js['nama_jabatan']}</option>"; ?></select></div>
    <div class="mb-3"><label>Periode</label><input type="text" name="periode_tahun" class="form-control" required placeholder="Contoh: 2024-2026"></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div></div>

<!-- Modal Tambah Majelis -->
<div class="modal fade" id="addMajelisModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Data Majelis</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="add"><input type="hidden" name="type" value="majelis">
    <div class="mb-3"><label>Jemaat</label><select name="id_jemaat" class="form-select" required><option value="">- Pilih Jemaat -</option><?php foreach($list_jemaat as $j) echo "<option value='{$j['id_jemaat']}'>{$j['nama_lengkap']}</option>"; ?></select></div>
    <div class="mb-3"><label>Jabatan Majelis</label><select name="id_jabatan" class="form-select" required><option value="">- Pilih Jabatan -</option><?php foreach($list_jab_majelis as $jm) echo "<option value='{$jm['id_jabatan']}'>{$jm['nama_jabatan']}</option>"; ?></select></div>
    <div class="mb-3"><label>Periode</label><input type="text" name="periode_tahun" class="form-control" required placeholder="Contoh: 2024-2026"></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div></div>

<?php require_once '../../includes/admin_footer.php'; ?>
