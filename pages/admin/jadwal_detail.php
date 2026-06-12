<?php
require_once '../../includes/admin_header.php';

$id_ibadah = (int)($_GET['id'] ?? 0);
if (!$id_ibadah) { header("Location: jadwal.php"); exit; }

$j = $pdo->prepare("SELECT j.*, s.nama_sektor FROM tblJadwalIbadah j LEFT JOIN tblSektor s ON j.id_sektor = s.id_sektor WHERE j.id_ibadah = ?");
$j->execute([$id_ibadah]);
$jadwal = $j->fetch();
if (!$jadwal) { header("Location: jadwal.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('jadwal_detail.php'); // H5
    $action = $_POST['action'] ?? '';

    // LITURGI
    if ($action === 'save_liturgi') {
        $model    = trim($_POST['model_liturgi'] ?? '');
        $bacaan   = trim($_POST['ayat_bacaan'] ?? '');
        $khotbah  = trim($_POST['ayat_khotbah'] ?? '');
        $ket      = trim($_POST['keterangan_liturgi'] ?? '');

        $cek = $pdo->prepare("SELECT id_liturgi FROM tblLiturgi WHERE id_ibadah = ?");
        $cek->execute([$id_ibadah]);
        if ($cek->fetch()) {
            $pdo->prepare("UPDATE tblLiturgi SET model_liturgi=?, ayat_bacaan=?, ayat_khotbah=?, keterangan_liturgi=? WHERE id_ibadah=?")
                ->execute([$model, $bacaan, $khotbah, $ket, $id_ibadah]);
        } else {
            $pdo->prepare("INSERT INTO tblLiturgi (id_ibadah, model_liturgi, ayat_bacaan, ayat_khotbah, keterangan_liturgi) VALUES (?, ?, ?, ?, ?)")
                ->execute([$id_ibadah, $model, $bacaan, $khotbah, $ket]);
        }
        $_SESSION['success_msg'] = "Liturgi berhasil disimpan.";
    }

    // NYANYIAN
    elseif ($action === 'add_nyanyian') {
        $sumber = $_POST['sumber_nyanyian'] ?? '';
        $nomor  = trim($_POST['nomor_lagu'] ?? '');
        $ayat   = trim($_POST['ayat_lagu'] ?? '');
        $urutan = (int)($_POST['urutan'] ?? 0);

        $pdo->prepare("INSERT INTO tblNyanyian (id_ibadah, sumber_nyanyian, nomor_lagu, ayat_lagu, urutan) VALUES (?, ?, ?, ?, ?)")
            ->execute([$id_ibadah, $sumber, $nomor, $ayat, $urutan]);
        $_SESSION['success_msg'] = "Nyanyian berhasil ditambahkan.";

    } elseif ($action === 'del_nyanyian') {
        $id_nyanyian = (int)($_POST['id_nyanyian'] ?? 0);
        $pdo->prepare("DELETE FROM tblNyanyian WHERE id_nyanyian = ?")->execute([$id_nyanyian]);
        $_SESSION['success_msg'] = "Nyanyian dihapus.";
    }

    // PETUGAS
    elseif ($action === 'add_petugas') {
        $id_peran  = (int)($_POST['id_peran_ibadah'] ?? 0);
        $id_jemaat = (int)($_POST['id_jemaat'] ?? 0);
        if ($id_peran && $id_jemaat) {
            $pdo->prepare("INSERT INTO tblPetugasIbadah (id_ibadah, id_peran_ibadah, id_jemaat) VALUES (?, ?, ?)")
                ->execute([$id_ibadah, $id_peran, $id_jemaat]);
            $_SESSION['success_msg'] = "Petugas ditambahkan.";
        }
    } elseif ($action === 'del_petugas') {
        $id_relasi = (int)($_POST['id_relasi'] ?? 0);
        $pdo->prepare("DELETE FROM tblPetugasIbadah WHERE id_relasi = ?")->execute([$id_relasi]);
        $_SESSION['success_msg'] = "Petugas dihapus.";
    }

    header("Location: jadwal_detail.php?id=$id_ibadah");
    exit;
}

// Data Fetch
$liturgi_row = $pdo->prepare("SELECT * FROM tblLiturgi WHERE id_ibadah = ?");
$liturgi_row->execute([$id_ibadah]);
$data_liturgi = $liturgi_row->fetch();

$nyanyian_rows = $pdo->prepare("SELECT * FROM tblNyanyian WHERE id_ibadah = ? ORDER BY urutan ASC, id_nyanyian ASC");
$nyanyian_rows->execute([$id_ibadah]);
$nyanyian = $nyanyian_rows->fetchAll();

$petugas_q = $pdo->prepare("SELECT p.*, j.nama_lengkap, r.nama_peran FROM tblPetugasIbadah p JOIN tblJemaat j ON p.id_jemaat = j.id_jemaat JOIN tblPeranIbadah r ON p.id_peran_ibadah = r.id_peran_ibadah WHERE p.id_ibadah = ? ORDER BY r.nama_peran");
$petugas_q->execute([$id_ibadah]);
$data_petugas = $petugas_q->fetchAll();

$list_peran  = $pdo->query("SELECT * FROM tblPeranIbadah ORDER BY nama_peran")->fetchAll();
$list_jemaat = $pdo->query("SELECT id_jemaat, nama_lengkap FROM tblJemaat WHERE status_keanggotaan='Aktif' ORDER BY nama_lengkap")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="jadwal.php" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h3 class="fw-bold m-0"><?= htmlspecialchars($jadwal['nama_ibadah']) ?></h3>
        <p class="text-muted m-0">
            <i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($jadwal['tanggal_waktu'])) ?>
            &nbsp;|&nbsp;
            <i class="fas fa-clock"></i> <?= date('H:i', strtotime($jadwal['tanggal_waktu'])) ?> WIB
            <?php if (!empty($jadwal['nama_sektor'])): ?>
                &nbsp;<span class="badge bg-secondary"><?= htmlspecialchars($jadwal['nama_sektor']) ?></span>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row">
    <!-- KOLOM KIRI: LITURGI & NYANYIAN -->
    <div class="col-lg-7 mb-4">
        <div class="glass-card-admin mb-4">
            <h5 class="fw-bold mb-3 border-bottom pb-2">Detail Liturgi</h5>
            <form method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="save_liturgi">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Model Liturgi</label>
                        <input type="text" name="model_liturgi" class="form-control"
                            value="<?= htmlspecialchars($data_liturgi['model_liturgi'] ?? '') ?>"
                            placeholder="Cth: Liturgi Minggu Advent I">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ayat Bacaan</label>
                        <input type="text" name="ayat_bacaan" class="form-control"
                            value="<?= htmlspecialchars($data_liturgi['ayat_bacaan'] ?? '') ?>"
                            placeholder="Cth: Yesaya 9:6">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ayat Khotbah</label>
                        <input type="text" name="ayat_khotbah" class="form-control"
                            value="<?= htmlspecialchars($data_liturgi['ayat_khotbah'] ?? '') ?>"
                            placeholder="Cth: Yohanes 3:16">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Keterangan / Catatan Liturgi</label>
                        <textarea name="keterangan_liturgi" class="form-control" rows="3"
                            placeholder="Info perjamuan kudus, baptisan, acara khusus, dll."><?= htmlspecialchars($data_liturgi['keterangan_liturgi'] ?? '') ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Liturgi</button>
            </form>
        </div>

        <div class="glass-card-admin">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold m-0">Daftar Nyanyian</h5>
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addNyanyian">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead><tr><th>#</th><th>Sumber</th><th>No. Lagu</th><th>Ayat</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php if (empty($nyanyian)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada nyanyian.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($nyanyian as $n): ?>
                        <tr>
                            <td class="text-muted"><?= $n['urutan'] ?: '-' ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($n['sumber_nyanyian']) ?></span></td>
                            <td class="fw-bold"><?= htmlspecialchars($n['nomor_lagu']) ?></td>
                            <td><?= htmlspecialchars($n['ayat_lagu']) ?></td>
                            <td>
                                <form method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="del_nyanyian">
                                    <input type="hidden" name="id_nyanyian" value="<?= $n['id_nyanyian'] ?>">
                                    <button class="btn btn-sm text-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: PETUGAS IBADAH -->
    <div class="col-lg-5 mb-4">
        <div class="glass-card-admin h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold m-0">Petugas Ibadah</h5>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPetugas">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
            <ul class="list-group list-group-flush">
                <?php if (empty($data_petugas)): ?>
                    <li class="list-group-item text-center text-muted">Belum ada petugas ditugaskan.</li>
                <?php endif; ?>
                <?php foreach ($data_petugas as $p): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold"><?= htmlspecialchars($p['nama_peran']) ?></div>
                        <?= htmlspecialchars($p['nama_lengkap']) ?>
                    </div>
                    <form method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="del_petugas">
                        <input type="hidden" name="id_relasi" value="<?= $p['id_relasi'] ?>">
                        <button class="btn btn-sm text-danger" title="Hapus"><i class="fas fa-times"></i></button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Modal Nyanyian -->
<div class="modal fade" id="addNyanyian" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Tambah Nyanyian</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                    <input type="hidden" name="action" value="add_nyanyian">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sumber Buku</label>
                        <select name="sumber_nyanyian" class="form-select" required>
                            <option value="Kidung Jemaat">Kidung Jemaat (KJ)</option>
                            <option value="Pelengkap Kidung Jemaat">Pelengkap Kidung Jemaat (PKJ)</option>
                            <option value="Nyanyikanlah Kidung Baru">Nyanyikanlah Kidung Baru (NKB)</option>
                            <option value="Buku Ende">Buku Ende</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-3">
                            <label class="form-label fw-semibold">Urutan</label>
                            <input type="number" name="urutan" class="form-control" min="1" placeholder="1">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">No. Lagu</label>
                            <input type="text" name="nomor_lagu" class="form-control" required placeholder="Cth: 15">
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold">Ayat</label>
                            <input type="text" name="ayat_lagu" class="form-control" placeholder="Cth: 1, 2, 4">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Petugas -->
<div class="modal fade" id="addPetugas" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Tugaskan Petugas Ibadah</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                    <input type="hidden" name="action" value="add_petugas">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Peran Ibadah</label>
                        <select name="id_peran_ibadah" class="form-select" required>
                            <option value="">-- Pilih Peran --</option>
                            <?php foreach ($list_peran as $r) echo "<option value='{$r['id_peran_ibadah']}'>" . htmlspecialchars($r['nama_peran']) . "</option>"; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jemaat Penugasan</label>
                        <select name="id_jemaat" class="form-select" required>
                            <option value="">-- Cari Jemaat --</option>
                            <?php foreach ($list_jemaat as $jm) echo "<option value='{$jm['id_jemaat']}'>" . htmlspecialchars($jm['nama_lengkap']) . "</option>"; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/admin_footer.php'; ?>
