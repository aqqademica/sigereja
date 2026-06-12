<?php
require_once '../../includes/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../actions/notifikasi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php"); exit;
}

$notif_count = hitung_notifikasi($pdo, $_SESSION['user_id']);
$notif_list  = ambil_notifikasi($pdo, $_SESSION['user_id'], 8);

// ── Fetch jadwal + liturgi detail (LEFT JOIN so jadwal shows even without liturgi) ──
$jadwal_mendatang = $pdo->query("
    SELECT j.id_ibadah, j.nama_ibadah, j.tanggal_waktu, s.nama_sektor,
           l.model_liturgi, l.ayat_bacaan, l.ayat_khotbah, l.keterangan_liturgi
    FROM tblJadwalIbadah j
    LEFT JOIN tblSektor   s ON j.id_sektor  = s.id_sektor
    LEFT JOIN tblLiturgi  l ON l.id_ibadah  = j.id_ibadah
    WHERE j.tanggal_waktu >= NOW()
    ORDER BY j.tanggal_waktu ASC
    LIMIT 30
")->fetchAll(PDO::FETCH_ASSOC);

$jadwal_lalu = $pdo->query("
    SELECT j.id_ibadah, j.nama_ibadah, j.tanggal_waktu, s.nama_sektor,
           l.model_liturgi, l.ayat_bacaan, l.ayat_khotbah, l.keterangan_liturgi
    FROM tblJadwalIbadah j
    LEFT JOIN tblSektor   s ON j.id_sektor  = s.id_sektor
    LEFT JOIN tblLiturgi  l ON l.id_ibadah  = j.id_ibadah
    WHERE j.tanggal_waktu < NOW()
    ORDER BY j.tanggal_waktu DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// ── Batch-fetch all nyanyian for the listed jadwal IDs ──
$all_ids = array_unique(array_merge(
    array_column($jadwal_mendatang, 'id_ibadah'),
    array_column($jadwal_lalu,      'id_ibadah')
));
$nyanyian_map = [];
if (!empty($all_ids)) {
    $in = implode(',', array_fill(0, count($all_ids), '?'));
    $sn = $pdo->prepare("SELECT * FROM tblNyanyian WHERE id_ibadah IN ($in) ORDER BY urutan ASC, id_nyanyian ASC");
    $sn->execute($all_ids);
    foreach ($sn->fetchAll(PDO::FETCH_ASSOC) as $n) {
        $nyanyian_map[$n['id_ibadah']][] = $n;
    }
}

// ── Batch-fetch petugas (khotbah + petugas lain) ──
$petugas_map = [];
if (!empty($all_ids)) {
    $in = implode(',', array_fill(0, count($all_ids), '?'));
    $sp = $pdo->prepare("
        SELECT p.id_ibadah, r.nama_peran, j.nama_lengkap
        FROM tblPetugasIbadah p
        JOIN tblPeranIbadah r ON p.id_peran_ibadah = r.id_peran_ibadah
        JOIN tblJemaat      j ON p.id_jemaat        = j.id_jemaat
        WHERE p.id_ibadah IN ($in)
        ORDER BY r.nama_peran ASC
    ");
    $sp->execute($all_ids);
    foreach ($sp->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $petugas_map[$p['id_ibadah']][] = $p;
    }
}

// ── Helpers ──
$hari_id  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan_id = [
    'January'=>'Januari','February'=>'Februari','March'=>'Maret',
    'April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli',
    'August'=>'Agustus','September'=>'September','October'=>'Oktober',
    'November'=>'November','December'=>'Desember'
];
function bId($s) { global $bulan_id; return str_replace(array_keys($bulan_id), array_values($bulan_id), $s); }

// Group upcoming by month
$grouped = [];
foreach ($jadwal_mendatang as $j) {
    $grouped[date('F Y', strtotime($j['tanggal_waktu']))][] = $j;
}

// Reusable card renderer
function renderCard(array $j, array $nyanyian_map, array $petugas_map, array $hari_id, bool $past = false): void {
    $ts    = strtotime($j['tanggal_waktu']);
    $hari  = $hari_id[date('w', $ts)];
    $tgl   = date('d', $ts);
    $waktu = date('H:i', $ts);
    $id    = $j['id_ibadah'];

    $nyanyian = $nyanyian_map[$id] ?? [];
    $petugas  = $petugas_map[$id]  ?? [];

    $hasDetail = !empty($j['model_liturgi']) || !empty($j['ayat_khotbah'])
              || !empty($j['ayat_bacaan'])   || !empty($j['keterangan_liturgi'])
              || !empty($nyanyian)            || !empty($petugas);

    $collapseId = 'detail-' . $id;
    $pastClass  = $past ? ' past-card' : '';
    $boxStyle   = $past ? 'background:rgba(255,255,255,.12);box-shadow:none;' : '';
    $numSize    = $past ? 'font-size:1.2rem;' : '';

    // Find preacher from petugas list (nama_peran contains 'Khotbah')
    $khotbah_nama = '';
    foreach ($petugas as $p) {
        if (stripos($p['nama_peran'], 'khotbah') !== false) {
            $khotbah_nama = $p['nama_lengkap'];
            break;
        }
    }
    ?>
    <div class="jadwal-card<?= $pastClass ?>" style="flex-direction:column;align-items:stretch;padding:0;overflow:hidden;">
        <!-- ── Header row ── -->
        <div class="d-flex align-items-center gap-3 p-3">
            <div class="jadwal-date-box" style="<?= $boxStyle ?>">
                <div class="day-num" style="<?= $numSize ?>"><?= $tgl ?></div>
                <div class="day-name"><?= $hari ?></div>
            </div>
            <div class="jadwal-info">
                <div class="jadwal-nama d-flex align-items-center gap-2 flex-wrap">
                    <span><?= htmlspecialchars($j['nama_ibadah']) ?></span>
                    <?php if ($khotbah_nama): ?>
                    <span class="jadwal-preacher">
                        <i class="fas fa-microphone me-1" style="opacity:.6;"></i>Khotbah Oleh
                        <strong><?= htmlspecialchars($khotbah_nama) ?></strong>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="jadwal-meta">
                    <?php if (!empty($j['nama_sektor'])): ?>
                        <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($j['nama_sektor']) ?>
                    <?php else: ?>
                        <i class="fas fa-church me-1"></i>Seluruh Jemaat
                    <?php endif; ?>
                    <?php if (!empty($j['ayat_khotbah'])): ?>
                        <span class="jadwal-meta-sep">·</span>
                        <i class="fas fa-bible me-1"></i><?= htmlspecialchars($j['ayat_khotbah']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                <div class="jadwal-time-badge <?= $past ? 'opacity-50' : '' ?>">
                    <i class="fas fa-clock me-1" style="opacity:.7;"></i><?= $waktu ?> WIB
                </div>
                <?php if ($hasDetail): ?>
                <button class="btn btn-sm detail-toggle collapsed"
                        data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>"
                        aria-expanded="false" style="font-size:.7rem;padding:.15rem .55rem;border-radius:20px;">
                    <i class="fas fa-chevron-down me-1"></i><span>Detail</span>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($hasDetail): ?>
        <!-- ── Collapsible detail ── -->
        <div class="collapse" id="<?= $collapseId ?>">
            <div class="detail-panel">

                <?php if (!empty($j['model_liturgi']) || !empty($j['keterangan_liturgi'])): ?>
                <div class="detail-row">
                    <div class="detail-section-title"><i class="fas fa-book-open me-2"></i>Liturgi</div>
                    <?php if (!empty($j['model_liturgi'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Model</span>
                        <span class="detail-value"><?= htmlspecialchars($j['model_liturgi']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($j['keterangan_liturgi'])): ?>
                    <div class="detail-item" style="align-items:flex-start;">
                        <span class="detail-label">Votum / Catatan</span>
                        <span class="detail-value" style="white-space:pre-line;"><?= htmlspecialchars($j['keterangan_liturgi']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($j['ayat_bacaan']) || !empty($j['ayat_khotbah'])): ?>
                <div class="detail-row">
                    <div class="detail-section-title"><i class="fas fa-bible me-2"></i>Firman Tuhan</div>
                    <?php if (!empty($j['ayat_bacaan'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Ayat Bacaan</span>
                        <span class="detail-value fw-semibold" style="color:hsl(35,90%,65%);"><?= htmlspecialchars($j['ayat_bacaan']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($j['ayat_khotbah'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Ayat Khotbah</span>
                        <span class="detail-value fw-semibold" style="color:hsl(35,90%,65%);"><?= htmlspecialchars($j['ayat_khotbah']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($petugas)): ?>
                <div class="detail-row">
                    <div class="detail-section-title"><i class="fas fa-users me-2"></i>Petugas Ibadah</div>
                    <?php foreach ($petugas as $p): ?>
                    <div class="detail-item">
                        <span class="detail-label"><?= htmlspecialchars($p['nama_peran']) ?></span>
                        <span class="detail-value"><?= htmlspecialchars($p['nama_lengkap']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($nyanyian)): ?>
                <div class="detail-row" style="border-bottom:none;padding-bottom:.5rem;">
                    <div class="detail-section-title"><i class="fas fa-music me-2"></i>Daftar Nyanyian</div>
                    <div class="nyanyian-list">
                        <?php foreach ($nyanyian as $idx => $n): ?>
                        <div class="nyanyian-row">
                            <span class="nyanyian-num"><?= $n['urutan'] ?: ($idx + 1) ?></span>
                            <span class="nyanyian-source"><?= htmlspecialchars($n['sumber_nyanyian']) ?></span>
                            <span class="nyanyian-num-lagu fw-bold" style="color:#fff;"><?= htmlspecialchars($n['nomor_lagu']) ?></span>
                            <?php if (!empty($n['ayat_lagu'])): ?>
                            <span class="nyanyian-ayat">Bait: <?= htmlspecialchars($n['ayat_lagu']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Jadwal Ibadah - SIGereja</title>
    <meta name="description" content="Jadwal ibadah, liturgi, dan nyanyian gereja.">
    <meta name="theme-color" content="#1e1b4b">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* ── Card shell ── */
        .jadwal-card {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 16px;
            margin-bottom: .75rem;
            transition: background .2s, transform .18s, box-shadow .2s;
            overflow: hidden;
        }
        .jadwal-card:hover { background: rgba(255,255,255,.09); }
        .past-card { opacity: .55; }

        /* ── Date box ── */
        .jadwal-date-box {
            min-width: 52px; text-align: center;
            background: linear-gradient(135deg, hsl(246,80%,55%), hsl(246,80%,38%));
            border-radius: 11px; padding: .45rem .3rem;
            box-shadow: 0 4px 14px rgba(79,70,229,.35); flex-shrink: 0;
        }
        .jadwal-date-box .day-num { font-size: 1.35rem; font-weight: 800; color: #fff; line-height: 1; }
        .jadwal-date-box .day-name { font-size: .58rem; font-weight: 600; color: rgba(255,255,255,.75); text-transform: uppercase; letter-spacing:.06em; }

        /* ── Info ── */
        .jadwal-info { flex: 1; min-width: 0; }
        .jadwal-nama {
            color: #fff; font-weight: 700; font-size: .95rem;
            overflow: hidden;
        }
        .jadwal-preacher {
            font-size: .72rem; font-weight: 500;
            color: rgba(255,255,255,.5);
            white-space: nowrap;
        }
        .jadwal-preacher strong { color: rgba(255,255,255,.75); font-weight: 600; }
        .jadwal-meta { color: rgba(255,255,255,.5); font-size: .76rem; margin-top:.1rem; }
        .jadwal-meta-sep { margin: 0 .3rem; opacity: .4; }

        /* ── Time badge ── */
        .jadwal-time-badge {
            background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
            border-radius: 20px; padding: .2rem .7rem;
            color: #fff; font-size: .74rem; font-weight: 600;
        }

        /* ── Toggle button ── */
        .detail-toggle {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            color: rgba(255,255,255,.65);
            transition: background .15s, color .15s;
        }
        .detail-toggle:not(.collapsed) { background: rgba(255,255,255,.15); color: #fff; }
        .detail-toggle:not(.collapsed) .fa-chevron-down { transform: rotate(180deg); }
        .detail-toggle .fa-chevron-down { transition: transform .25s; }
        .detail-toggle:not(.collapsed) span { /* hide text when open */ }

        /* ── Detail panel ── */
        .detail-panel {
            border-top: 1px solid rgba(255,255,255,.08);
            background: rgba(0,0,0,.18);
        }
        .detail-row {
            padding: .85rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .detail-section-title {
            font-size: .68rem; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: hsl(35,90%,58%);
            margin-bottom: .55rem;
        }
        .detail-item {
            display: flex; align-items: center; gap: .75rem;
            padding: .25rem 0; font-size: .83rem;
        }
        .detail-label {
            color: rgba(255,255,255,.45); min-width: 110px; flex-shrink: 0; font-size: .78rem;
        }
        .detail-value { color: rgba(255,255,255,.88); }

        /* ── Month header ── */
        .jadwal-month-header {
            color: hsl(35,90%,58%); font-size: .72rem; font-weight: 700;
            letter-spacing: .12em; text-transform: uppercase;
            padding: .6rem 0 .4rem; border-bottom: 1px solid rgba(255,255,255,.07);
            margin-bottom: .6rem; margin-top: .5rem;
        }
        .jadwal-month-header:first-child { margin-top: 0; }

        /* ── Nyanyian list ── */
        .nyanyian-list { display: flex; flex-direction: column; gap: .3rem; }
        .nyanyian-row {
            display: flex; align-items: center; gap: .6rem;
            background: rgba(255,255,255,.04); border-radius: 8px;
            padding: .35rem .65rem; font-size: .8rem;
        }
        .nyanyian-num {
            min-width: 22px; text-align: center; font-weight: 700;
            color: rgba(255,255,255,.35); font-size: .7rem;
        }
        .nyanyian-source {
            background: rgba(255,255,255,.1); border-radius: 20px;
            padding: .1rem .5rem; font-size: .68rem; color: rgba(255,255,255,.6);
            white-space: nowrap;
        }
        .nyanyian-num-lagu { min-width: 32px; }
        .nyanyian-ayat { color: rgba(255,255,255,.45); font-size: .74rem; }

        /* ── Section glass ── */
        .section-glass {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 20px; padding: 1.25rem 1.25rem;
        }
        .empty-state { text-align: center; padding: 3rem 1rem; color: rgba(255,255,255,.35); }
        .empty-state i { font-size: 2.8rem; margin-bottom: .75rem; display: block; }
    </style>
</head>
<body>
<?php include '../../includes/navbar.php'; ?>
<main>
<div class="container py-4 fade-in-up" style="max-width:720px;">

    <div class="mb-4">
        <h2 class="hero-title fw-800 mb-1"><i class="fas fa-calendar-alt me-2"></i>Jadwal Ibadah</h2>
        <p class="hero-subtitle small mb-0">
            Jadwal ibadah, liturgi, dan pelayanan yang akan datang.
        </p>
    </div>

    <!-- ══ Jadwal Mendatang ══ -->
    <div class="section-glass mb-4">
        <h5 class="fw-bold mb-4" style="color:#fff;">
            <i class="fas fa-clock me-2" style="color:hsl(35,90%,58%);"></i>Jadwal Mendatang
        </h5>

        <?php if (empty($jadwal_mendatang)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>Belum ada jadwal ibadah yang akan datang.</p>
        </div>
        <?php else: ?>
            <?php foreach ($grouped as $bulan => $items): ?>
            <div class="jadwal-month-header"><?= bId($bulan) ?></div>
            <?php foreach ($items as $j): ?>
                <?php renderCard($j, $nyanyian_map, $petugas_map, $hari_id, false); ?>
            <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ══ Jadwal Lalu ══ -->
    <?php if (!empty($jadwal_lalu)): ?>
    <div class="section-glass">
        <h6 class="fw-bold mb-3" style="color:rgba(255,255,255,.45);">
            <i class="fas fa-history me-2"></i>Ibadah Sebelumnya
        </h6>
        <?php foreach ($jadwal_lalu as $j): ?>
            <?php renderCard($j, $nyanyian_map, $petugas_map, $hari_id, true); ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</main>
<?php include '../../includes/footer.php'; ?>
