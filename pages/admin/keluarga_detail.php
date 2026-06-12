<?php
require_once '../../includes/admin_header.php';

$id_keluarga = $_GET['id'] ?? '';
if (!$id_keluarga) {
    header("Location: keluarga.php");
    exit;
}

$stmt_k = $pdo->prepare("SELECT * FROM tblKeluarga WHERE id_keluarga = ?");
$stmt_k->execute([$id_keluarga]);
$keluarga = $stmt_k->fetch();

if (!$keluarga) {
    header("Location: keluarga.php");
    exit;
}

// Set Kepala Keluarga
csrf_verify('keluarga_detail.php'); // H5
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_kepala'])) {
    $id_jemaat = $_POST['id_jemaat'] ?? '';
    if ($id_jemaat) {
        $update = $pdo->prepare("UPDATE tblKeluarga SET id_kepala_keluarga = ? WHERE id_keluarga = ?");
        $update->execute([$id_jemaat, $id_keluarga]);
        $_SESSION['success_msg'] = "Kepala keluarga berhasil diatur.";
        header("Location: keluarga_detail.php?id=$id_keluarga");
        exit;
    }
}

// Remove from Family
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_member'])) {
    $id_jemaat = $_POST['id_jemaat'] ?? '';
    if ($id_jemaat) {
        $update = $pdo->prepare("UPDATE tblJemaat SET id_keluarga = NULL, status_dalam_keluarga = NULL WHERE id_jemaat = ?");
        $update->execute([$id_jemaat]);
        
        // If removed was head, reset head
        if ($keluarga['id_kepala_keluarga'] == $id_jemaat) {
            $pdo->prepare("UPDATE tblKeluarga SET id_kepala_keluarga = NULL WHERE id_keluarga = ?")->execute([$id_keluarga]);
        }
        
        $_SESSION['success_msg'] = "Anggota berhasil dikeluarkan dari keluarga ini.";
        header("Location: keluarga_detail.php?id=$id_keluarga");
        exit;
    }
}

$anggota = $pdo->prepare("SELECT * FROM tblJemaat WHERE id_keluarga = ? ORDER BY tanggal_lahir ASC");
$anggota->execute([$id_keluarga]);
$list_anggota = $anggota->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="keluarga.php" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h2 class="fw-bold mb-0">Detail Keluarga: <?= htmlspecialchars($keluarga['nomor_kk']) ?></h2>
    </div>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="glass-card-admin">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold m-0"><i class="fas fa-users text-primary me-2"></i> Daftar Anggota Keluarga</h5>
        <a href="jemaat.php?add_to_kk=<?= $id_keluarga ?>" class="btn btn-sm btn-primary-custom"><i class="fas fa-user-plus me-1"></i>Tambah Anggota</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle border">
            <thead class="table-light">
                <tr>
                    <th>Nama Jemaat</th>
                    <th>Status dlm Keluarga</th>
                    <th>Jenis Kelamin</th>
                    <th>Usia</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($list_anggota)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada anggota keluarga. Tambahkan melalui menu Data Jemaat.</td></tr>
                <?php endif; ?>
                
                <?php foreach ($list_anggota as $j): 
                    $bday = new DateTime($j['tanggal_lahir']);
                    $today = new DateTime('today');
                    $age = $bday->diff($today)->y;
                ?>
                <tr class="<?= ($keluarga['id_kepala_keluarga'] == $j['id_jemaat']) ? 'table-primary bg-opacity-25' : '' ?>">
                    <td>
                        <?= htmlspecialchars($j['nama_lengkap']) ?>
                        <?php if ($keluarga['id_kepala_keluarga'] == $j['id_jemaat']): ?>
                            <span class="badge bg-primary ms-1"><i class="fas fa-star me-1"></i>Kepala Keluarga</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($j['status_dalam_keluarga']) ?></td>
                    <td><?= $j['jenis_kelamin'] ?></td>
                    <td><?= $age ?> Tahun</td>
                    <td class="text-center">
                        <?php if ($keluarga['id_kepala_keluarga'] != $j['id_jemaat']): ?>
                        <form method="POST" class="d-inline">
    <?= csrf_field() ?>
<input type="hidden" name="set_kepala" value="1">
                            <input type="hidden" name="id_jemaat" value="<?= $j['id_jemaat'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-success" title="Jadikan Kepala Keluarga"><i class="fas fa-user-tie"></i></button>
                        </form>
                        <?php endif; ?>
                        
                        <form method="POST" class="d-inline" onsubmit="return confirm('Keluarkan dari keluarga ini?');">
    <?= csrf_field() ?>
<input type="hidden" name="remove_member" value="1">
                            <input type="hidden" name="id_jemaat" value="<?= $j['id_jemaat'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Keluarkan dari KK"><i class="fas fa-user-minus"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/admin_footer.php'; ?>
