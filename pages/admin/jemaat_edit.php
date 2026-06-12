<?php
require_once '../../includes/admin_header.php';
require_once '../../actions/notifikasi.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) { header("Location: jemaat.php"); exit; }

$stmt = $pdo->prepare("SELECT j.*, k.id_kepala_keluarga FROM tblJemaat j LEFT JOIN tblKeluarga k ON j.id_keluarga = k.id_keluarga WHERE j.id_jemaat = ?");
$stmt->execute([$id]);
$j = $stmt->fetch();
if (!$j) { header("Location: jemaat.php"); exit; }

$sektors   = $pdo->query("SELECT * FROM tblSektor ORDER BY nama_sektor")->fetchAll();
$keluargas = $pdo->query("SELECT id_keluarga, nomor_kk FROM tblKeluarga ORDER BY nomor_kk")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('jemaat_edit.php'); // H5
    $nama            = trim($_POST['nama_lengkap'] ?? '');
    $jk              = $_POST['jenis_kelamin'] ?? '';
    $tgl_lahir       = $_POST['tanggal_lahir'] ?? '';
    $status_sidi     = $_POST['status_sidi'] ?? 'Tidak';
    $status_nikah    = $_POST['status_pernikahan'] ?? '';
    $id_sektor       = $_POST['id_sektor'] ?: NULL;
    $alamat          = $_POST['alamat'] ?? '';
    $no_hp           = $_POST['no_hp'] ?? '';
    $email           = $_POST['alamat_email_aktif'] ?? '';
    $status_keanggotaan = $_POST['status_keanggotaan'] ?? 'Aktif';
    $asal_gereja     = $_POST['asal_gereja'] ?? '';
    $status_dlm_kk   = $_POST['status_dalam_keluarga'] ?? '';

    $pdo->prepare("UPDATE tblJemaat SET nama_lengkap=?,jenis_kelamin=?,tanggal_lahir=?,status_sidi=?,status_pernikahan=?,id_sektor=?,alamat=?,no_hp=?,alamat_email_aktif=?,status_keanggotaan=?,asal_gereja=?,status_dalam_keluarga=? WHERE id_jemaat=?")
        ->execute([$nama,$jk,$tgl_lahir,$status_sidi,$status_nikah,$id_sektor,$alamat,$no_hp,$email,$status_keanggotaan,$asal_gereja,$status_dlm_kk,$id]);

    // Handle deceased & head of family reassignment
    if ($status_keanggotaan === 'Meninggal Dunia' && $j['id_keluarga'] && $j['id_jemaat'] == $j['id_kepala_keluarga']) {
        // Reset head of family
        $pdo->prepare("UPDATE tblKeluarga SET id_kepala_keluarga = NULL WHERE id_keluarga = ?")
            ->execute([$j['id_keluarga']]);
        $_SESSION['warning_msg'] = "⚠️ Kepala Keluarga telah meninggal. Silakan atur Kepala Keluarga baru di halaman Detail Keluarga.";
    }

    // Send notification to admins
    $admins = $pdo->query("SELECT id FROM users WHERE role IN ('Super Admin','Sekretaris') AND status_verifikasi='Approved Majelis'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($admins as $admin_id) {
        kirim_notifikasi($pdo, $admin_id, "Data Jemaat Diupdate", "Data {$nama} telah diperbarui.", "jemaat_view.php?id={$id}");
    }

    $_SESSION['success_msg'] = "Data jemaat berhasil diperbarui.";
    header("Location: jemaat_view.php?id={$id}"); exit;
}
?>

<div class="mb-3">
    <a href="jemaat_view.php?id=<?= $id ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Kembali ke Profil</a>
</div>

<h2 class="fw-bold mb-4"><i class="fas fa-edit me-2 text-primary"></i>Edit Data Jemaat: <?= htmlspecialchars($j['nama_lengkap']) ?></h2>

<?php if (isset($_SESSION['warning_msg'])): ?>
<div class="alert alert-warning alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['warning_msg']; unset($_SESSION['warning_msg']); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<form method="POST">
    <?= csrf_field() ?>
<div class="card-admin mb-4">
    <div class="card-header"><h5><i class="fas fa-user me-2 text-primary"></i>Data Pribadi</h5></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" class="form-control" required value="<?= htmlspecialchars($j['nama_lengkap']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Jenis Kelamin *</label>
                <select name="jenis_kelamin" class="form-select" required>
                    <option value="Laki-laki"  <?= $j['jenis_kelamin']==='Laki-laki'  ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="Perempuan"   <?= $j['jenis_kelamin']==='Perempuan'   ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Lahir *</label>
                <input type="date" name="tanggal_lahir" class="form-control" required value="<?= $j['tanggal_lahir'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Status Pernikahan *</label>
                <select name="status_pernikahan" class="form-select" required>
                    <?php foreach(['Belum Menikah','Sudah Menikah','Cerai Hidup','Cerai Mati'] as $opt): ?>
                    <option <?= $j['status_pernikahan']===$opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Sudah Sidi?</label>
                <select name="status_sidi" class="form-select">
                    <option value="Tidak" <?= $j['status_sidi']==='Tidak' ? 'selected' : '' ?>>Tidak</option>
                    <option value="Ya"    <?= $j['status_sidi']==='Ya'    ? 'selected' : '' ?>>Ya</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Status Keanggotaan</label>
                <select name="status_keanggotaan" class="form-select" id="statusKeanggotaan">
                    <?php foreach(['Aktif','Meninggal Dunia','Pindah Gereja (Mutasi)','Tidak Ada Keterangan'] as $opt): ?>
                    <option <?= $j['status_keanggotaan']===$opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
                <!-- Warning for deceased -->
                <div id="deceasedWarning" class="alert alert-danger mt-2 small py-2" style="display:none;">
                    <i class="fas fa-exclamation-triangle me-1"></i>Jika kepala keluarga meninggal, sistem akan mereset Kepala KK secara otomatis.
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">No. HP</label>
                <input type="tel" name="no_hp" class="form-control" value="<?= htmlspecialchars($j['no_hp']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="alamat_email_aktif" class="form-control" value="<?= htmlspecialchars($j['alamat_email_aktif']) ?>">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($j['alamat']) ?></textarea>
            </div>
        </div>
    </div>
</div>

<div class="card-admin mb-4">
    <div class="card-header"><h5><i class="fas fa-home me-2 text-primary"></i>Data Keluarga & Sektor</h5></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Status dalam KK</label>
                <select name="status_dalam_keluarga" class="form-select">
                    <?php foreach(['Kepala Keluarga','Suami','Istri','Anak','Lainnya'] as $opt): ?>
                    <option <?= $j['status_dalam_keluarga']===$opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Sektor</label>
                <select name="id_sektor" class="form-select">
                    <option value="">-- Pilih --</option>
                    <?php foreach($sektors as $s): ?>
                    <option value="<?= $s['id_sektor'] ?>" <?= $j['id_sektor']==$s['id_sektor']?'selected':'' ?>><?= htmlspecialchars($s['nama_sektor']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Asal Gereja (Pindahan)</label>
                <input type="text" name="asal_gereja" class="form-control" value="<?= htmlspecialchars($j['asal_gereja'] ?? '') ?>">
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-3">
    <button type="submit" class="btn btn-primary-custom btn-lg px-5"><i class="fas fa-save me-2"></i>Simpan Perubahan</button>
    <a href="jemaat_view.php?id=<?= $id ?>" class="btn btn-lg btn-outline-secondary">Batal</a>
</div>
</form>

<script>
document.getElementById('statusKeanggotaan').addEventListener('change', function() {
    document.getElementById('deceasedWarning').style.display = this.value === 'Meninggal Dunia' ? '' : 'none';
});
</script>

<?php require_once '../../includes/admin_footer.php'; ?>
