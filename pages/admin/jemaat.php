<?php
require_once '../../includes/admin_header.php';

$add_to_kk = $_GET['add_to_kk'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('jemaat.php'); // H5
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $nama_lengkap          = trim($_POST['nama_lengkap'] ?? '');
        $jenis_kelamin         = $_POST['jenis_kelamin'] ?? '';
        $tanggal_lahir         = $_POST['tanggal_lahir'] ?? '';
        $status_sidi           = $_POST['status_sidi'] ?? 'Tidak';
        $status_pernikahan     = $_POST['status_pernikahan'] ?? '';
        $status_baptis         = $_POST['status_baptis'] ?? 'Belum';
        $id_sektor             = $_POST['id_sektor'] ?: NULL;
        $no_hp                 = $_POST['no_hp'] ?? '';
        $email                 = trim($_POST['alamat_email_aktif'] ?? '');
        $pekerjaan             = trim($_POST['pekerjaan'] ?? '');
        $asal_gereja           = $_POST['asal_gereja'] ?? '';
        $alasan_pindah         = $_POST['alasan_pindah'] ?? '';
        $status_keanggotaan    = $_POST['status_keanggotaan'] ?? 'Aktif';
        $id_keluarga           = !empty($_POST['id_keluarga']) ? $_POST['id_keluarga'] : NULL;
        $status_dalam_keluarga = !empty($_POST['status_dalam_keluarga']) ? $_POST['status_dalam_keluarga'] : NULL;

        // Alamat: build combined KTP + Domisili (same logic as keluarga_add.php)
        $alamat_ktp    = trim($_POST['alamat'] ?? '');
        $domisili_sama = ($_POST['domisili_sama'] ?? '') === 'ya';
        $alamat_dom    = $domisili_sama ? $alamat_ktp : trim($_POST['alamat_domisili'] ?? $alamat_ktp);
        $alamat        = 'KTP: ' . $alamat_ktp;
        if (!$domisili_sama && $alamat_dom !== $alamat_ktp) {
            $alamat .= ' | Domisili: ' . $alamat_dom;
        }

        if ($nama_lengkap && $jenis_kelamin && $tanggal_lahir) {
            $stmt = $pdo->prepare("INSERT INTO tblJemaat 
                (nama_lengkap, jenis_kelamin, tanggal_lahir, status_baptis, status_sidi, status_pernikahan,
                 pekerjaan, id_sektor, alamat, no_hp, alamat_email_aktif, asal_gereja, alasan_pindah,
                 status_keanggotaan, id_keluarga, status_dalam_keluarga) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $nama_lengkap, $jenis_kelamin, $tanggal_lahir, $status_baptis, $status_sidi, $status_pernikahan,
                $pekerjaan, $id_sektor, $alamat, $no_hp, $email, $asal_gereja, $alasan_pindah,
                $status_keanggotaan, $id_keluarga, $status_dalam_keluarga
            ]);
            $_SESSION['success_msg'] = "Data Jemaat berhasil ditambahkan.";

            if ($id_keluarga && isset($_POST['redirect_detail'])) {
                header("Location: keluarga_detail.php?id=" . $id_keluarga);
                exit;
            }
        }
    } elseif ($action === 'delete') {
        $id_jemaat = $_POST['id_jemaat'] ?? '';
        if ($id_jemaat) {
            $pdo->prepare("DELETE FROM tblJemaat WHERE id_jemaat = ?")->execute([$id_jemaat]);
            $_SESSION['success_msg'] = "Data Jemaat dihapus.";
        }
    }
    
    header("Location: jemaat.php");
    exit;
}

// Fetch Sektors and Keluarga for dropdowns
$sektors = $pdo->query("SELECT * FROM tblSektor ORDER BY nama_sektor")->fetchAll();
$keluargas = $pdo->query("SELECT * FROM tblKeluarga ORDER BY nomor_kk")->fetchAll();

$limit = 50;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_jemaat = $pdo->query("SELECT COUNT(*) FROM tblJemaat")->fetchColumn();
$total_pages = ceil($total_jemaat / $limit);

// Fetch Jemaat
$jemaats = $pdo->query("
    SELECT j.*, s.nama_sektor, k.nomor_kk 
    FROM tblJemaat j 
    LEFT JOIN tblSektor s ON j.id_sektor = s.id_sektor 
    LEFT JOIN tblKeluarga k ON j.id_keluarga = k.id_keluarga
    ORDER BY j.nama_lengkap
    LIMIT $limit OFFSET $offset
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="fas fa-users me-2 text-primary"></i>Manajemen Data Jemaat</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus me-2"></i>Tambah Jemaat
    </button>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="glass-card-admin">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nama Lengkap</th>
                    <th>L/P</th>
                    <th>Usia</th>
                    <th>Sektor</th>
                    <th>Status</th>
                    <th>No. KK</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jemaats as $j): 
                    $bday = new DateTime($j['tanggal_lahir']);
                    $age = $bday->diff(new DateTime('today'))->y;
                ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($j['nama_lengkap']) ?></td>
                    <td><?= $j['jenis_kelamin'] == 'Laki-laki' ? 'L' : 'P' ?></td>
                    <td><?= $age ?> Thn</td>
                    <td><?= $j['nama_sektor'] ?? '-' ?></td>
                    <td>
                        <?php if($j['status_keanggotaan'] == 'Aktif'): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php elseif($j['status_keanggotaan'] == 'Pindah Gereja (Mutasi)'): ?>
                            <span class="badge bg-warning text-dark">Mutasi</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= $j['status_keanggotaan'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= $j['nomor_kk'] ?? '-' ?></td>
                    <td class="text-center">
                        <a href="jemaat_view.php?id=<?= $j['id_jemaat'] ?>" class="btn btn-sm btn-outline-info me-1" title="Detail"><i class="fas fa-eye"></i></a>
                        <a href="jemaat_edit.php?id=<?= $j['id_jemaat'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="fas fa-edit"></i></a>
                        <button class="btn btn-sm btn-outline-danger btn-delete-jemaat" title="Hapus"
                            data-id="<?= $j['id_jemaat'] ?>"
                            data-nama="<?= htmlspecialchars($j['nama_lengkap']) ?>">
                            <i class="fas fa-trash"></i>
                        </button>
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

<!-- Add Modal — placed OUTSIDE table -->
<div class="modal fade" id="addModal" tabindex="-1" <?= $add_to_kk ? 'data-bs-backdrop="static" data-bs-keyboard="false"' : '' ?>>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:var(--alt-sidebar-bg);color:#fff;">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>
                    <?= $add_to_kk ? 'Tambah Anggota Keluarga' : 'Registrasi Jemaat Baru' ?>
                </h5>
                <?php if (!$add_to_kk): ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                <?php else: ?>
                    <a href="keluarga_detail.php?id=<?= $add_to_kk ?>" class="btn-close btn-close-white"></a>
                <?php endif; ?>
            </div>
            <form method="POST" id="addJemaatForm" novalidate>
    <?= csrf_field() ?>
<div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <?php if ($add_to_kk): ?>
                        <input type="hidden" name="redirect_detail" value="1">
                        <div class="alert alert-info py-2 mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Menambahkan anggota ke Keluarga terpilih. Isi semua data dengan lengkap.
                        </div>
                    <?php endif; ?>

                    <!-- ── Identitas Utama ── -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required autofocus>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" id="aj_jenis_kelamin" class="form-select" required>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_lahir" class="form-control" required>
                        </div>
                    </div>

                    <!-- ── Status ── -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status Pernikahan <span class="text-danger">*</span></label>
                            <select name="status_pernikahan" id="aj_status_nikah" class="form-select" required>
                                <option value="Belum Menikah">Belum Menikah</option>
                                <option value="Sudah Menikah">Sudah Menikah</option>
                                <option value="Cerai Hidup">Cerai Hidup</option>
                                <option value="Cerai Mati">Cerai Mati</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status Baptis</label>
                            <select name="status_baptis" id="aj_status_baptis" class="form-select">
                                <option value="Belum">Belum Dibaptis</option>
                                <option value="Sudah">Sudah Dibaptis</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sudah Sidi?</label>
                            <select name="status_sidi" id="aj_status_sidi" class="form-select">
                                <option value="Tidak">Tidak</option>
                                <option value="Ya">Ya</option>
                            </select>
                        </div>
                    </div>

                    <!-- ── Kontak ── -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">No. HP <span class="text-danger">*</span>
                                <span class="text-muted fw-normal">(7–15 digit)</span>
                            </label>
                            <input type="tel" name="no_hp" id="aj_no_hp" class="form-control"
                                required maxlength="15" minlength="7" inputmode="numeric"
                                placeholder="08xxxxxxxxxx"
                                oninput="this.value=this.value.replace(/[^0-9+\-\s]/g,'').slice(0,15);
                                         const d=this.value.replace(/\D/g,'');
                                         this.classList.toggle('is-invalid', this.value.length>0 && d.length<7);
                                         this.classList.toggle('is-valid', d.length>=7);">
                            <div class="invalid-feedback">No. HP wajib diisi (7–15 digit).</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="alamat_email_aktif" class="form-control"
                                placeholder="contoh@email.com"
                                oninput="this.classList.toggle('is-invalid', this.value.length>0 && !this.validity.valid);">
                            <div class="invalid-feedback">Format email tidak valid.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pekerjaan</label>
                            <input type="text" name="pekerjaan" class="form-control" placeholder="Guru, Wiraswasta, dll.">
                        </div>
                    </div>

                    <!-- ── Alamat ── -->
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat KTP <span class="text-danger">*</span></label>
                            <textarea name="alamat" id="aj_alamat_ktp" class="form-control" rows="2" required
                                placeholder="Alamat sesuai Kartu Tanda Penduduk..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="aj_domisili_sama"
                                    name="domisili_sama" value="ya" checked>
                                <label class="form-check-label fw-semibold" for="aj_domisili_sama">
                                    <i class="fas fa-check-circle text-success me-1"></i>Alamat Domisili sama dengan Alamat KTP
                                </label>
                            </div>
                            <div id="aj_domisili_wrap" style="display:none;">
                                <label class="form-label fw-semibold">Alamat Domisili <span class="text-danger">*</span></label>
                                <textarea name="alamat_domisili" id="aj_alamat_domisili" class="form-control" rows="2"
                                    placeholder="Alamat tempat tinggal saat ini..."></textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- ── Relasi Keluarga ── -->
                    <h6 class="fw-bold mb-3"><i class="fas fa-sitemap me-2 text-primary"></i>Relasi Keluarga &amp; Keanggotaan</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Keluarga (No. KK)</label>
                            <select name="id_keluarga" class="form-select">
                                <option value="">-- Tidak Tautkan --</option>
                                <?php foreach ($keluargas as $k): ?>
                                    <option value="<?= $k['id_keluarga'] ?>" <?= ($add_to_kk == $k['id_keluarga']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nomor_kk']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Hubungan Dalam Keluarga</label>
                            <select name="status_dalam_keluarga" id="aj_status_dlm" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="Kepala Keluarga">Kepala Keluarga</option>
                                <option value="Suami">Suami</option>
                                <option value="Istri">Istri</option>
                                <option value="Anak">Anak</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sektor</label>
                            <select name="id_sektor" class="form-select">
                                <?php if ($add_to_kk): ?>
                                    <option value="">-- Ikut Kepala KK --</option>
                                <?php else: ?>
                                    <option value="">-- Pilih Sektor --</option>
                                <?php endif; ?>
                                <?php foreach ($sektors as $s): ?>
                                    <option value="<?= $s['id_sektor'] ?>"><?= htmlspecialchars($s['nama_sektor']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Keanggotaan</label>
                            <select name="status_keanggotaan" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Meninggal Dunia">Meninggal Dunia</option>
                                <option value="Pindah Gereja (Mutasi)">Pindah Gereja (Mutasi)</option>
                                <option value="Tidak Ada Keterangan">Tidak Ada Keterangan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Asal Gereja <span class="text-muted fw-normal">(Jika Pindahan)</span></label>
                            <input type="text" name="asal_gereja" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <?php if (!$add_to_kk): ?>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-save me-1"></i>Simpan Jemaat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal — SINGLE SHARED, placed OUTSIDE table (fixes mouse click bug) -->
<div class="modal fade" id="deleteModalShared" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Hapus Jemaat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_jemaat" id="deleteJemaatId">
                    <p>Yakin ingin menghapus <strong id="deleteJemaatNama"></strong> dari database?</p>
                    <div class="alert alert-warning small mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Aksi ini tidak dapat dibatalkan.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Delete modal pre-fill ──
document.querySelectorAll('.btn-delete-jemaat').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('deleteJemaatId').value       = this.dataset.id;
        document.getElementById('deleteJemaatNama').textContent = this.dataset.nama;
        var modal = new bootstrap.Modal(document.getElementById('deleteModalShared'));
        modal.show();
    });
});

// ── Add modal: auto-logic for Status Hubungan Dalam Keluarga ──
(function () {
    var statusDlm   = document.getElementById('aj_status_dlm');
    var jkSel       = document.getElementById('aj_jenis_kelamin');
    var nikahSel    = document.getElementById('aj_status_nikah');
    var baptisSel   = document.getElementById('aj_status_baptis');
    var sidiSel     = document.getElementById('aj_status_sidi');

    if (!statusDlm) return;

    statusDlm.addEventListener('change', function () {
        var val = this.value;
        if (val === 'Istri') {
            jkSel.value     = 'Perempuan';
            nikahSel.value  = 'Sudah Menikah';
            baptisSel.value = 'Sudah';
            sidiSel.value   = 'Ya';
        } else if (val === 'Suami' || val === 'Kepala Keluarga') {
            jkSel.value = 'Laki-laki';
        }
    });

    // ── Domisili toggle ──
    var domCheck = document.getElementById('aj_domisili_sama');
    var domWrap  = document.getElementById('aj_domisili_wrap');
    var domArea  = document.getElementById('aj_alamat_domisili');

    function syncDomisili() {
        var same = domCheck.checked;
        domWrap.style.display = same ? 'none' : '';
        if (domArea) domArea.required = !same;
    }
    domCheck.addEventListener('change', syncDomisili);
    if (domArea) {
        domArea.addEventListener('input', function () {
            if (this.value.trim()) domCheck.checked = false;
            syncDomisili();
        });
    }
    syncDomisili();

    // ── Form submit validation ──
    document.getElementById('addJemaatForm').addEventListener('submit', function (e) {
        var hp = document.getElementById('aj_no_hp');
        var digits = hp ? hp.value.replace(/\D/g, '') : '';
        var emailEl = this.querySelector('input[type="email"]');
        var valid = true;

        if (hp && digits.length < 7) {
            hp.classList.add('is-invalid');
            valid = false;
        }
        if (emailEl && emailEl.value.length > 0 && !emailEl.validity.valid) {
            emailEl.classList.add('is-invalid');
            valid = false;
        }
        if (!valid) {
            e.preventDefault();
            this.querySelector('.is-invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
})();
</script>

<?php if ($add_to_kk): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var addModal = new bootstrap.Modal(document.getElementById('addModal'));
    addModal.show();
});
</script>
<?php endif; ?>

<?php require_once '../../includes/admin_footer.php'; ?>

