<?php
require_once '../../includes/admin_header.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) { header("Location: jemaat.php"); exit; }

$stmt = $pdo->prepare("
    SELECT j.*, s.nama_sektor, k.nomor_kk,
           kk.nama_lengkap AS kepala_kk
    FROM tblJemaat j
    LEFT JOIN tblSektor s ON j.id_sektor = s.id_sektor
    LEFT JOIN tblKeluarga k ON j.id_keluarga = k.id_keluarga
    LEFT JOIN tblJemaat kk ON k.id_kepala_keluarga = kk.id_jemaat
    WHERE j.id_jemaat = ?
");
$stmt->execute([$id]);
$j = $stmt->fetch();
if (!$j) { header("Location: jemaat.php"); exit; }

$bday = new DateTime($j['tanggal_lahir']);
$age  = $bday->diff(new DateTime('today'))->y;

$keahlians = $pdo->prepare("SELECT k.nama_keahlian FROM tblKeahlian_Pelayan kp JOIN tblKeahlian k ON kp.id_keahlian = k.id_keahlian WHERE kp.id_jemaat = ?");
$keahlians->execute([$id]);
$keahlian_list = $keahlians->fetchAll(PDO::FETCH_COLUMN);

$seksis_jemaat = $pdo->prepare("SELECT s.nama_seksi FROM tblPengurusSeksi ps JOIN tblSeksi s ON ps.id_seksi = s.id_seksi WHERE ps.id_jemaat = ?");
$seksis_jemaat->execute([$id]);
$seksi_list = $seksis_jemaat->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="mb-4">
    <a href="jemaat.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
</div>

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="card-admin text-center p-4">
            <div style="width:80px;height:80px;background:linear-gradient(135deg,hsl(246,80%,60%),hsl(246,80%,40%));border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:2rem;color:#fff;font-weight:800;box-shadow:0 8px 24px rgba(79,70,229,.3);">
                <?= strtoupper(substr($j['nama_lengkap'],0,1)) ?>
            </div>
            <h4 class="fw-bold mb-1"><?= htmlspecialchars($j['nama_lengkap']) ?></h4>
            <p class="text-muted small mb-3"><?= $j['jenis_kelamin'] ?> &bull; <?= $age ?> Tahun</p>

            <?php
            $status_color = ['Aktif'=>'success','Meninggal Dunia'=>'dark','Pindah Gereja (Mutasi)'=>'warning'];
            $color = $status_color[$j['status_keanggotaan']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $color ?> mb-3 px-3 py-2"><?= htmlspecialchars($j['status_keanggotaan']) ?></span>

            <div class="d-grid gap-2 mt-3">
                <a href="jemaat_edit.php?id=<?= $id ?>" class="btn btn-primary-custom"><i class="fas fa-edit me-2"></i>Edit Data</a>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="col-lg-8">
        <div class="card-admin mb-3">
            <div class="card-header"><h5><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Pribadi</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $fields = [
                        'Tanggal Lahir'    => date('d M Y', strtotime($j['tanggal_lahir'])) . " ({$age} tahun)",
                        'Status Pernikahan' => $j['status_pernikahan'],
                        'Status Sidi'      => $j['status_sidi'],
                        'No. HP'           => $j['no_hp'] ?: '-',
                        'Email'            => $j['alamat_email_aktif'] ?: '-',
                        'Alamat'           => $j['alamat'] ?: '-',
                    ];
                    foreach ($fields as $label => $val): ?>
                    <div class="col-md-6">
                        <div class="text-muted small fw-semibold mb-1"><?= $label ?></div>
                        <div class="fw-medium"><?= htmlspecialchars($val) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card-admin mb-3">
            <div class="card-header"><h5><i class="fas fa-home me-2 text-primary"></i>Data Keluarga & Sektor</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-muted small fw-semibold mb-1">No. KK</div><div class="fw-medium"><?= htmlspecialchars($j['nomor_kk'] ?? '-') ?></div></div>
                    <div class="col-md-4"><div class="text-muted small fw-semibold mb-1">Status dalam KK</div><div class="fw-medium"><?= htmlspecialchars($j['status_dalam_keluarga'] ?? '-') ?></div></div>
                    <div class="col-md-4"><div class="text-muted small fw-semibold mb-1">Sektor</div><div class="fw-medium"><?= htmlspecialchars($j['nama_sektor'] ?? '-') ?></div></div>
                    <?php if (!empty($seksi_list)): ?>
                    <div class="col-12">
                        <div class="text-muted small fw-semibold mb-1">Seksi</div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($seksi_list as $s): ?><span class="badge bg-primary"><?= htmlspecialchars($s) ?></span><?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($keahlian_list)): ?>
                    <div class="col-12">
                        <div class="text-muted small fw-semibold mb-1">Keahlian</div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($keahlian_list as $k): ?><span class="badge bg-info text-dark"><?= htmlspecialchars($k) ?></span><?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/admin_footer.php'; ?>
