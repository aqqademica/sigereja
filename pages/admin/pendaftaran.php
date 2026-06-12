<?php
require_once '../../includes/admin_header.php';

// Verifikator / Admin Role Check (optional, adjust if needed)
$is_admin = in_array($_SESSION['role'], ['Super Admin', 'Admin Sistem', 'Sekretaris', 'Pendeta']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('pendaftaran.php'); // H5
    $action = $_POST['action'] ?? '';
    $type = $_POST['type'] ?? '';
    $status = $_POST['status_approval'] ?? '';
    
    // ACTION BAPTIS
    if ($type === 'baptis') {
        if ($action === 'add') {
            $id_keluarga = $_POST['id_keluarga'] ?? '';
            $nama = $_POST['nama_anak'] ?? '';
            $tempat = $_POST['tempat_lahir'] ?? '';
            $tgl_lahir = $_POST['tanggal_lahir'] ?? '';
            $jk = $_POST['jenis_kelamin'] ?? '';
            $tgl_pelaksanaan = $_POST['tanggal_pelaksanaan'] ?: null;
            
            $pdo->prepare("INSERT INTO tblPendaftaranBaptis (id_keluarga, nama_anak, tempat_lahir, tanggal_lahir, jenis_kelamin, tanggal_pelaksanaan) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$id_keluarga, $nama, $tempat, $tgl_lahir, $jk, $tgl_pelaksanaan]);
            $_SESSION['success_msg'] = "Pendaftaran Baptis berhasil disimpan.";
        } elseif ($action === 'update_status') {
            $id = $_POST['id_baptis'];
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE tblPendaftaranBaptis SET status_approval = ? WHERE id_baptis = ?")->execute([$status, $id]);
                
                // Trigger Auto Insert ke tblJemaat jika Selesai
                if ($status === 'Selesai') {
                    $b = $pdo->prepare("SELECT * FROM tblPendaftaranBaptis WHERE id_baptis = ?");
                    $b->execute([$id]);
                    $baptis = $b->fetch();
                    
                    // Cek agar tidak terinsert ganda jika user memencet Selesai berkali-kali
                    $check = $pdo->prepare("SELECT id_jemaat FROM tblJemaat WHERE nama_lengkap = ? AND id_keluarga = ? AND tanggal_lahir = ?");
                    $check->execute([$baptis['nama_anak'], $baptis['id_keluarga'], $baptis['tanggal_lahir']]);
                    if (!$check->fetch()) {
                        // Inherit id_sektor from parent (M4 fix)
                        $q_sektor = $pdo->prepare("SELECT id_sektor FROM tblJemaat WHERE id_keluarga = ? AND status_dalam_keluarga IN ('Kepala Keluarga', 'Suami') LIMIT 1");
                        $q_sektor->execute([$baptis['id_keluarga']]);
                        $sektor_parent = $q_sektor->fetchColumn();
                        $id_sektor_anak = $sektor_parent ?: null;

                        $pdo->prepare("INSERT INTO tblJemaat (id_keluarga, id_sektor, status_dalam_keluarga, nama_lengkap, jenis_kelamin, tanggal_lahir, status_baptis, status_sidi, status_pernikahan) VALUES (?, ?, 'Anak', ?, ?, ?, 'Sudah', 'Tidak', 'Belum Menikah')")
                            ->execute([$baptis['id_keluarga'], $id_sektor_anak, $baptis['nama_anak'], $baptis['jenis_kelamin'], $baptis['tanggal_lahir']]);
                    }
                }
                $pdo->commit();
                $_SESSION['success_msg'] = "Status Baptis diupdate.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error_msg'] = "Gagal memproses.";
            }
        }
    }
    
    // ACTION SIDI
    elseif ($type === 'sidi') {
        if ($action === 'add') {
            $id_jemaat = $_POST['id_jemaat'] ?? '';
            $id_keluarga = $_POST['id_keluarga'] ?? '';
            $tgl = $_POST['tanggal_pelaksanaan'] ?: null;
            
            if (!$id_jemaat || !$id_keluarga) {
                $_SESSION['error_msg'] = "Keluarga dan Jemaat wajib dipilih.";
            } else {
                try {
                    $pdo->prepare("INSERT INTO tblPendaftaranSidi (id_jemaat, id_keluarga, tanggal_pelaksanaan) VALUES (?, ?, ?)")
                        ->execute([$id_jemaat, $id_keluarga, $tgl]);
                    $_SESSION['success_msg'] = "Pendaftaran Sidi berhasil disimpan.";
                } catch (Exception $e) {
                    $_SESSION['error_msg'] = "Terjadi kesalahan saat mendaftar Sidi.";
                }
            }
        } elseif ($action === 'update_status') {
            $id = $_POST['id_sidi'];
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE tblPendaftaranSidi SET status_approval = ? WHERE id_sidi = ?")->execute([$status, $id]);
                if ($status === 'Selesai') {
                    $s = $pdo->prepare("SELECT id_jemaat FROM tblPendaftaranSidi WHERE id_sidi = ?");
                    $s->execute([$id]);
                    $id_jem = $s->fetchColumn();
                    $pdo->prepare("UPDATE tblJemaat SET status_sidi = 'Ya' WHERE id_jemaat = ?")->execute([$id_jem]);
                }
                $pdo->commit();
                $_SESSION['success_msg'] = "Status Sidi diupdate.";
            } catch (Exception $e) {
                $pdo->rollBack();
            }
        }
    }
    
    // ACTION NIKAH
    elseif ($type === 'nikah') {
        if ($action === 'add') {
            $pria = $_POST['id_jemaat_pria'] ?? '';
            $wanita = $_POST['id_jemaat_wanita'] ?? '';
            $tgl = $_POST['tanggal_pelaksanaan'] ?: null;
            $tempat = $_POST['tempat_pelaksanaan'] ?? '';
            if ($pria == $wanita) {
                $_SESSION['error_msg'] = "Mempelai pria dan wanita tidak boleh orang yang sama.";
            } else {
                $pdo->prepare("INSERT INTO tblPendaftaranNikah (id_jemaat_pria, id_jemaat_wanita, tanggal_pelaksanaan, tempat_pelaksanaan) VALUES (?, ?, ?, ?)")
                    ->execute([$pria, $wanita, $tgl, $tempat]);
                $_SESSION['success_msg'] = "Pendaftaran Pernikahan berhasil disimpan.";
            }
        } elseif ($action === 'update_status') {
            $id = $_POST['id_nikah'];
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE tblPendaftaranNikah SET status_approval = ? WHERE id_nikah = ?")->execute([$status, $id]);
                if ($status === 'Selesai') {
                    $n = $pdo->prepare("SELECT id_jemaat_pria, id_jemaat_wanita FROM tblPendaftaranNikah WHERE id_nikah = ?");
                    $n->execute([$id]);
                    $nikah = $n->fetch();
                    $pdo->prepare("UPDATE tblJemaat SET status_pernikahan = 'Sudah Menikah' WHERE id_jemaat IN (?, ?)")
                        ->execute([$nikah['id_jemaat_pria'], $nikah['id_jemaat_wanita']]);
                }
                $pdo->commit();
                $_SESSION['success_msg'] = "Status Pernikahan diupdate.";
            } catch (Exception $e) {
                $pdo->rollBack();
            }
        }
    }
    
    header("Location: pendaftaran.php");
    exit;
}

// Fetch lists for forms
$keluargas = $pdo->query("SELECT * FROM tblKeluarga ORDER BY nomor_kk")->fetchAll();
$jemaats = $pdo->query("SELECT id_jemaat, nama_lengkap, jenis_kelamin FROM tblJemaat WHERE status_keanggotaan='Aktif' ORDER BY nama_lengkap")->fetchAll();

$prias = array_filter($jemaats, fn($j) => $j['jenis_kelamin'] === 'Laki-laki');
$wanitas = array_filter($jemaats, fn($j) => $j['jenis_kelamin'] === 'Perempuan');

// Fetch Data for Tables
$baptis = $pdo->query("SELECT b.*, k.nomor_kk FROM tblPendaftaranBaptis b JOIN tblKeluarga k ON b.id_keluarga = k.id_keluarga ORDER BY b.created_at DESC")->fetchAll();
$sidi = $pdo->query("SELECT s.*, j.nama_lengkap, k.nomor_kk FROM tblPendaftaranSidi s JOIN tblJemaat j ON s.id_jemaat = j.id_jemaat JOIN tblKeluarga k ON s.id_keluarga = k.id_keluarga ORDER BY s.id_sidi DESC")->fetchAll();
$nikah = $pdo->query("SELECT n.*, jp.nama_lengkap as pria, jw.nama_lengkap as wanita FROM tblPendaftaranNikah n JOIN tblJemaat jp ON n.id_jemaat_pria = jp.id_jemaat JOIN tblJemaat jw ON n.id_jemaat_wanita = jw.id_jemaat ORDER BY n.id_nikah DESC")->fetchAll();

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Pendaftaran Pelayanan</h2>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#baptis">Baptisan Kudus</button></li>
    <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#sidi">Angkat Sidi</button></li>
    <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#nikah">Pernikahan</button></li>
</ul>

<div class="tab-content bg-transparent border-0 p-0 shadow-none">
    
    <!-- BAPTIS -->
    <div class="tab-pane fade show active glass-card-admin" id="baptis">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0">Data Baptisan Anak</h5>
            <button class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addBaptisModal"><i class="fas fa-plus"></i> Tambah</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Keluarga (KK)</th><th>Nama Anak</th><th>TTL</th><th>Jk</th><th>Tanggal Baptis</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach($baptis as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['nomor_kk']) ?></td>
                        <td><?= htmlspecialchars($b['nama_anak']) ?></td>
                        <td><?= htmlspecialchars($b['tempat_lahir']) ?>, <?= date('d M Y', strtotime($b['tanggal_lahir'])) ?></td>
                        <td><?= $b['jenis_kelamin'] == 'Laki-laki' ? 'L' : 'P' ?></td>
                        <td><?= $b['tanggal_pelaksanaan'] ? date('d M Y', strtotime($b['tanggal_pelaksanaan'])) : '-' ?></td>
                        <td>
                            <?php 
                            $badge = ['Pending'=>'warning','Approved'=>'info','Selesai'=>'success','Ditolak'=>'danger'];
                            echo "<span class='badge bg-{$badge[$b['status_approval']]} text-dark'>{$b['status_approval']}</span>";
                            ?>
                        </td>
                        <td>
                            <?php if($b['status_approval'] != 'Selesai'): ?>
                            <form method="POST" class="d-inline">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="update_status"><input type="hidden" name="type" value="baptis"><input type="hidden" name="id_baptis" value="<?= $b['id_baptis'] ?>">
                                <select name="status_approval" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto; display:inline-block;">
                                    <option value="Pending" <?= $b['status_approval']=='Pending'?'selected':'' ?>>Pending</option>
                                    <option value="Approved" <?= $b['status_approval']=='Approved'?'selected':'' ?>>Approved</option>
                                    <option value="Selesai" <?= $b['status_approval']=='Selesai'?'selected':'' ?>>Selesai</option>
                                    <option value="Ditolak" <?= $b['status_approval']=='Ditolak'?'selected':'' ?>>Ditolak</option>
                                </select>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SIDI -->
    <div class="tab-pane fade glass-card-admin" id="sidi">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0">Data Angkat Sidi</h5>
            <button class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addSidiModal"><i class="fas fa-plus"></i> Tambah</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Nama Anak (Jemaat)</th><th>Keluarga Pengaju (KK)</th><th>Tgl Pelaksanaan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach($sidi as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($s['nomor_kk']) ?></td>
                        <td><?= $s['tanggal_pelaksanaan'] ? date('d M Y', strtotime($s['tanggal_pelaksanaan'])) : '-' ?></td>
                        <td>
                            <?php 
                            $badge = ['Pending'=>'warning','Approved'=>'info','Selesai'=>'success','Ditolak'=>'danger'];
                            echo "<span class='badge bg-{$badge[$s['status_approval']]} text-dark'>{$s['status_approval']}</span>";
                            ?>
                        </td>
                        <td>
                            <?php if($s['status_approval'] != 'Selesai'): ?>
                            <form method="POST" class="d-inline">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="update_status"><input type="hidden" name="type" value="sidi"><input type="hidden" name="id_sidi" value="<?= $s['id_sidi'] ?>">
                                <select name="status_approval" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto; display:inline-block;">
                                    <option value="Pending" <?= $s['status_approval']=='Pending'?'selected':'' ?>>Pending</option>
                                    <option value="Approved" <?= $s['status_approval']=='Approved'?'selected':'' ?>>Approved</option>
                                    <option value="Selesai" <?= $s['status_approval']=='Selesai'?'selected':'' ?>>Selesai</option>
                                    <option value="Ditolak" <?= $s['status_approval']=='Ditolak'?'selected':'' ?>>Ditolak</option>
                                </select>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- NIKAH -->
    <div class="tab-pane fade glass-card-admin" id="nikah">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0">Data Pernikahan</h5>
            <button class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addNikahModal"><i class="fas fa-plus"></i> Tambah</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Mempelai Pria</th><th>Mempelai Wanita</th><th>Waktu</th><th>Tempat</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach($nikah as $n): ?>
                    <tr>
                        <td><?= htmlspecialchars($n['pria']) ?></td>
                        <td><?= htmlspecialchars($n['wanita']) ?></td>
                        <td><?= $n['tanggal_pelaksanaan'] ? date('d M Y H:i', strtotime($n['tanggal_pelaksanaan'])) : '-' ?></td>
                        <td><?= htmlspecialchars($n['tempat_pelaksanaan']) ?></td>
                        <td>
                            <?php 
                            $badge = ['Pending'=>'warning','Approved'=>'info','Selesai'=>'success','Ditolak'=>'danger'];
                            echo "<span class='badge bg-{$badge[$n['status_approval']]} text-dark'>{$n['status_approval']}</span>";
                            ?>
                        </td>
                        <td>
                            <?php if($n['status_approval'] != 'Selesai'): ?>
                            <form method="POST" class="d-inline">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="update_status"><input type="hidden" name="type" value="nikah"><input type="hidden" name="id_nikah" value="<?= $n['id_nikah'] ?>">
                                <select name="status_approval" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto; display:inline-block;">
                                    <option value="Pending" <?= $n['status_approval']=='Pending'?'selected':'' ?>>Pending</option>
                                    <option value="Approved" <?= $n['status_approval']=='Approved'?'selected':'' ?>>Approved</option>
                                    <option value="Selesai" <?= $n['status_approval']=='Selesai'?'selected':'' ?>>Selesai</option>
                                    <option value="Ditolak" <?= $n['status_approval']=='Ditolak'?'selected':'' ?>>Ditolak</option>
                                </select>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modals -->
<div class="modal fade" id="addBaptisModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Daftar Baptisan Anak</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="add"><input type="hidden" name="type" value="baptis">
    <div class="mb-3"><label>Keluarga (KK)</label><select name="id_keluarga" class="form-select" required><option value="">- Pilih Keluarga -</option><?php foreach($keluargas as $k) echo "<option value='{$k['id_keluarga']}'>{$k['nomor_kk']}</option>"; ?></select></div>
    <div class="mb-3"><label>Nama Anak</label><input type="text" name="nama_anak" class="form-control" required></div>
    <div class="row"><div class="col-6 mb-3"><label>Tempat Lahir</label><input type="text" name="tempat_lahir" class="form-control" required></div><div class="col-6 mb-3"><label>Tgl Lahir</label><input type="date" name="tanggal_lahir" class="form-control" required></div></div>
    <div class="mb-3"><label>Jenis Kelamin</label><select name="jenis_kelamin" class="form-select"><option value="Laki-laki">Laki-laki</option><option value="Perempuan">Perempuan</option></select></div>
    <div class="mb-3"><label>Rencana Tanggal Pelaksanaan (Opsional)</label><input type="date" name="tanggal_pelaksanaan" class="form-control"></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div></div>

<div class="modal fade" id="addSidiModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Daftar Angkat Sidi</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="add"><input type="hidden" name="type" value="sidi">
    <div class="mb-3"><label>Keluarga Pengaju</label><select name="id_keluarga" class="form-select" required><option value="">- Pilih Keluarga -</option><?php foreach($keluargas as $k) echo "<option value='{$k['id_keluarga']}'>{$k['nomor_kk']}</option>"; ?></select></div>
    <div class="mb-3"><label>Nama Anak/Remaja (Data Jemaat)</label><select name="id_jemaat" class="form-select" required><option value="">- Pilih Jemaat -</option><?php foreach($jemaats as $j) echo "<option value='{$j['id_jemaat']}'>{$j['nama_lengkap']}</option>"; ?></select></div>
    <div class="mb-3"><label>Rencana Tanggal Pelaksanaan (Opsional)</label><input type="date" name="tanggal_pelaksanaan" class="form-control"></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div></div>

<div class="modal fade" id="addNikahModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Daftar Pernikahan</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST">
    <?= csrf_field() ?>
<div class="modal-body"><input type="hidden" name="action" value="add"><input type="hidden" name="type" value="nikah">
    <p class="text-muted small">Catatan: Jika mempelai dari luar, wajib didaftarkan di Data Jemaat terlebih dahulu.</p>
    <div class="mb-3"><label>Mempelai Pria</label><select name="id_jemaat_pria" class="form-select" required><option value="">- Pilih Jemaat Pria -</option><?php foreach($prias as $j) echo "<option value='{$j['id_jemaat']}'>{$j['nama_lengkap']}</option>"; ?></select></div>
    <div class="mb-3"><label>Mempelai Wanita</label><select name="id_jemaat_wanita" class="form-select" required><option value="">- Pilih Jemaat Wanita -</option><?php foreach($wanitas as $j) echo "<option value='{$j['id_jemaat']}'>{$j['nama_lengkap']}</option>"; ?></select></div>
    <div class="mb-3"><label>Tgl & Waktu Pelaksanaan</label><input type="datetime-local" name="tanggal_pelaksanaan" class="form-control"></div>
    <div class="mb-3"><label>Tempat</label><input type="text" name="tempat_pelaksanaan" class="form-control" placeholder="Gereja / Gedung"></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div></div>

<?php require_once '../../includes/admin_footer.php'; ?>
