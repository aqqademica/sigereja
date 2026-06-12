<?php
require_once '../../includes/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../actions/notifikasi.php';
require_once __DIR__ . '/../../includes/csrf.php'; // Fix missing csrf include

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php"); exit;
}

$notif_count = hitung_notifikasi($pdo, $_SESSION['user_id']);
$notif_list  = ambil_notifikasi($pdo, $_SESSION['user_id'], 8);

// Data for the user's family
$id_user = $_SESSION['user_id'];
$jemaat_user = $pdo->prepare("SELECT j.*, k.id_keluarga, k.nomor_kk FROM tblJemaat j LEFT JOIN tblKeluarga k ON j.id_keluarga = k.id_keluarga WHERE j.user_id = ?");
$jemaat_user->execute([$id_user]);
$my_jemaat = $jemaat_user->fetch();

// Handle "Daftar Kegiatan"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'daftar_sacrament') {
    csrf_verify('pendaftaran.php');
    if (!$my_jemaat) {
        $_SESSION['error_msg'] = "Pendaftaran gagal. Akun Anda belum terhubung dengan data jemaat. Silakan hubungi admin.";
        header("Location: pendaftaran.php"); exit;
    }
    
    $type         = $_POST['type'] ?? '';
    $id_jemaat    = (int) ($_POST['id_jemaat'] ?? 0);
    $id_keluarga  = (int) ($_POST['id_keluarga'] ?? 0);
    $tgl          = $_POST['tanggal_pelaksanaan'] ?? null;

    if ($type === 'baptis' && $id_keluarga) {
        $nama  = trim($_POST['nama_anak'] ?? '');
        $tempat= trim($_POST['tempat_lahir'] ?? '');
        $tl    = $_POST['tanggal_lahir'] ?? '';
        $jk    = $_POST['jenis_kelamin'] ?? 'Laki-laki';
        $pdo->prepare("INSERT INTO tblPendaftaranBaptis (id_keluarga,nama_anak,tempat_lahir,tanggal_lahir,jenis_kelamin,tanggal_pelaksanaan) VALUES (?,?,?,?,?,?)")
            ->execute([$id_keluarga,$nama,$tempat,$tl,$jk,$tgl]);
        $admins = $pdo->query("SELECT id FROM users WHERE role IN('Super Admin','Sekretaris') AND status_verifikasi='Approved Majelis'")->fetchAll(PDO::FETCH_COLUMN);
        foreach($admins as $aid) kirim_notifikasi($pdo,$aid,"Pendaftaran Baptis Baru","Pendaftaran baptis anak '{$nama}' diterima.","../../pages/admin/pendaftaran.php");
        $_SESSION['success_msg'] = "Pendaftaran Baptis berhasil dikirim dan menunggu verifikasi.";
    } elseif ($type === 'sidi') {
        if ($id_jemaat && $id_keluarga) {
            $pdo->prepare("INSERT INTO tblPendaftaranSidi (id_jemaat,id_keluarga,tanggal_pelaksanaan) VALUES (?,?,?)")
                ->execute([$id_jemaat,$id_keluarga,$tgl]);
            $_SESSION['success_msg'] = "Pendaftaran Sidi berhasil dikirim.";
        } else {
            $_SESSION['error_msg'] = "Pendaftaran gagal. Data keluarga atau jemaat tidak valid.";
        }
    } elseif ($type === 'nikah') {
        $pria   = (int) ($_POST['id_jemaat_pria'] ?? 0);
        $wanita = (int) ($_POST['id_jemaat_wanita'] ?? 0);
        $tempat = trim($_POST['tempat_pelaksanaan'] ?? '');
        if ($pria && $wanita && $pria !== $wanita) {
            $pdo->prepare("INSERT INTO tblPendaftaranNikah (id_jemaat_pria,id_jemaat_wanita,tanggal_pelaksanaan,tempat_pelaksanaan) VALUES (?,?,?,?)")
                ->execute([$pria,$wanita,$tgl,$tempat]);
            $_SESSION['success_msg'] = "Pendaftaran Pernikahan berhasil dikirim.";
        }
    }
    header("Location: pendaftaran.php"); exit;
}

$wanitas = array_filter($jemaats, fn($j) => $j['jenis_kelamin']==='Perempuan');

// Upcoming sacrament schedules
$jadwal_mendatang = $pdo->query("SELECT * FROM tblJadwalIbadah WHERE tanggal_waktu >= NOW() ORDER BY tanggal_waktu ASC LIMIT 5")->fetchAll();
$kegiatan_mendatang = $pdo->query("SELECT * FROM tblKegiatan WHERE status='Akan Dilaksanakan' AND tanggal_pelaksanaan >= NOW() ORDER BY tanggal_pelaksanaan ASC LIMIT 5")->fetchAll();

// My pending registrations
$my_baptis = [];
$my_sidi   = [];
$my_nikah  = [];
if ($my_jemaat) {
    $mb = $pdo->prepare("SELECT * FROM tblPendaftaranBaptis WHERE id_keluarga=? ORDER BY created_at DESC");
    $mb->execute([$my_jemaat['id_keluarga']]);
    $my_baptis = $mb->fetchAll();

    $ms = $pdo->prepare("SELECT * FROM tblPendaftaranSidi WHERE id_jemaat=? ORDER BY id_sidi DESC");
    $ms->execute([$my_jemaat['id_jemaat']]);
    $my_sidi = $ms->fetchAll();

    $mn = $pdo->prepare("SELECT n.*,jp.nama_lengkap as pria,jw.nama_lengkap as wanita FROM tblPendaftaranNikah n JOIN tblJemaat jp ON n.id_jemaat_pria=jp.id_jemaat JOIN tblJemaat jw ON n.id_jemaat_wanita=jw.id_jemaat WHERE n.id_jemaat_pria=? OR n.id_jemaat_wanita=? ORDER BY id_nikah DESC");
    $mn->execute([$my_jemaat['id_jemaat'],$my_jemaat['id_jemaat']]);
    $my_nikah = $mn->fetchAll();
}

$badge_map = ['Pending'=>'warning','Approved'=>'info','Selesai'=>'success','Ditolak'=>'danger'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Pendaftaran Sakramen - SIGereja</title>
    <meta name="theme-color" content="#1e1b4b">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php include '../../includes/navbar.php'; ?>
<main>
<div class="container py-4 fade-in-up">
    <h2 class="hero-title fw-800 mb-1"><i class="fas fa-hand-holding-heart me-2"></i>Pendaftaran Sakramen</h2>
    <p class="hero-subtitle mb-4 small">Daftar untuk pelayanan Baptisan, Sidi, dan Pernikahan langsung dari sini.</p>

    <?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (!$my_jemaat): ?>
    <div class="alert alert-warning">
        <i class="fas fa-info-circle me-2"></i><strong>Akun Tamu:</strong> Akun Anda saat ini belum terhubung dengan Kartu Keluarga Jemaat di sistem kami. Anda hanya dapat melihat warta dan jadwal ibadah. Untuk mendaftar sakramen, silakan hubungi Sekretariat Gereja untuk mengaitkan akun Anda.
    </div>
    <?php else: ?>
    <!-- Registration Tabs -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item"><button class="nav-link active px-4" data-bs-toggle="tab" data-bs-target="#tab-baptis">Baptisan</button></li>
        <li class="nav-item"><button class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#tab-sidi">Sidi</button></li>
        <li class="nav-item"><button class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#tab-nikah">Pernikahan</button></li>
        <li class="nav-item"><button class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#tab-riwayat">Riwayat Saya</button></li>
    </ul>

    <div class="tab-content">
        <!-- BAPTIS -->
        <div class="tab-pane fade show active" id="tab-baptis">
            <div class="glass-card" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);">
                <h5 class="fw-bold mb-3" style="color:#fff;"><i class="fas fa-baby me-2 text-warning"></i>Pendaftaran Baptisan Anak</h5>
                <form method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="daftar_sacrament">
                    <input type="hidden" name="type" value="baptis">
                    <input type="hidden" name="id_keluarga" value="<?= $my_jemaat['id_keluarga'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Nama Anak *</label>
                            <input type="text" name="nama_anak" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Tempat Lahir *</label>
                            <input type="text" name="tempat_lahir" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Tanggal Lahir *</label>
                            <input type="date" name="tanggal_lahir" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select"><option>Laki-laki</option><option>Perempuan</option></select></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Rencana Tanggal Baptis</label>
                            <input type="date" name="tanggal_pelaksanaan" class="form-control"></div>
                    </div>
                    <div class="mt-3"><button type="submit" class="btn btn-accent-custom"><i class="fas fa-paper-plane me-2"></i>Kirim Pendaftaran Baptis</button></div>
                </form>
            </div>
        </div>

        <!-- SIDI -->
        <div class="tab-pane fade" id="tab-sidi">
            <div class="glass-card" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);">
                <h5 class="fw-bold mb-3" style="color:#fff;"><i class="fas fa-praying-hands me-2 text-warning"></i>Pendaftaran Angkat Sidi</h5>
                <form method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="daftar_sacrament">
                    <input type="hidden" name="type" value="sidi">
                    <input type="hidden" name="id_keluarga" value="<?= $my_jemaat['id_keluarga'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Nama Jemaat yang akan Disidi *</label>
                            <select name="id_jemaat" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach($jemaats as $j) echo "<option value='{$j['id_jemaat']}'>{$j['nama_lengkap']}</option>"; ?>
                            </select></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Rencana Tanggal Sidi</label>
                            <input type="date" name="tanggal_pelaksanaan" class="form-control"></div>
                    </div>
                    <div class="mt-3"><button type="submit" class="btn btn-accent-custom"><i class="fas fa-paper-plane me-2"></i>Kirim Pendaftaran Sidi</button></div>
                </form>
            </div>
        </div>

        <!-- NIKAH -->
        <div class="tab-pane fade" id="tab-nikah">
            <div class="glass-card" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);">
                <h5 class="fw-bold mb-3" style="color:#fff;"><i class="fas fa-rings-wedding me-2 text-warning"></i>Pendaftaran Pernikahan</h5>
                <form method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="daftar_sacrament">
                    <input type="hidden" name="type" value="nikah">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Mempelai Pria *</label>
                            <select name="id_jemaat_pria" class="form-select" required><option value="">-- Pilih --</option><?php foreach($prias as $p) echo "<option value='{$p['id_jemaat']}'>{$p['nama_lengkap']}</option>"; ?></select></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Mempelai Wanita *</label>
                            <select name="id_jemaat_wanita" class="form-select" required><option value="">-- Pilih --</option><?php foreach($wanitas as $w) echo "<option value='{$w['id_jemaat']}'>{$w['nama_lengkap']}</option>"; ?></select></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Rencana Tanggal & Waktu</label>
                            <input type="datetime-local" name="tanggal_pelaksanaan" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold" style="color:rgba(255,255,255,.8);">Tempat Pernikahan</label>
                            <input type="text" name="tempat_pelaksanaan" class="form-control" placeholder="Gereja / Gedung"></div>
                    </div>
                    <div class="mt-3"><button type="submit" class="btn btn-accent-custom"><i class="fas fa-paper-plane me-2"></i>Kirim Pendaftaran Pernikahan</button></div>
                </form>
            </div>
        </div>

        <!-- RIWAYAT -->
        <div class="tab-pane fade" id="tab-riwayat">
            <div class="glass-card mb-3" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);">
                <h6 class="fw-bold mb-3" style="color:#fff;">Riwayat Pendaftaran Baptis</h6>
                <?php if (empty($my_baptis)): ?><p class="text-muted small">Belum ada pendaftaran baptis.</p>
                <?php else: foreach($my_baptis as $b): ?>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color:rgba(255,255,255,.1)!important;">
                    <div><div style="color:#fff;font-weight:600;"><?= htmlspecialchars($b['nama_anak']) ?></div>
                    <div class="small" style="color:rgba(255,255,255,.5);"><?= $b['tanggal_pelaksanaan'] ? date('d M Y',strtotime($b['tanggal_pelaksanaan'])) : 'Belum ditentukan' ?></div></div>
                    <span class="badge bg-<?= $badge_map[$b['status_approval']] ?? 'secondary' ?>"><?= $b['status_approval'] ?></span>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <?php if (!empty($my_sidi)): ?>
            <div class="glass-card mb-3" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);">
                <h6 class="fw-bold mb-3" style="color:#fff;">Riwayat Sidi</h6>
                <?php foreach($my_sidi as $s): ?>
                <div class="d-flex justify-content-between py-2">
                    <div style="color:rgba(255,255,255,.7);"><?= $s['tanggal_pelaksanaan'] ? date('d M Y',strtotime($s['tanggal_pelaksanaan'])) : 'Belum ditentukan' ?></div>
                    <span class="badge bg-<?= $badge_map[$s['status_approval']] ?? 'secondary' ?>"><?= $s['status_approval'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; // end !$my_jemaat check ?>
</div>
</main>
<?php include '../../includes/footer.php'; ?>
