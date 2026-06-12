<?php
require_once '../../includes/admin_header.php';
require_once '../../actions/notifikasi.php';

// Fetch masters
$sektors      = $pdo->query("SELECT * FROM tblSektor ORDER BY nama_sektor")->fetchAll();
$seksis       = $pdo->query("SELECT * FROM tblSeksi ORDER BY nama_seksi")->fetchAll();
$keahlians    = $pdo->query("SELECT * FROM tblKeahlian ORDER BY nama_keahlian")->fetchAll();
$peran_ibadah = $pdo->query("SELECT * FROM tblPeranIbadah ORDER BY nama_peran")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('keluarga_add.php'); // H5
    $nomor_kk = trim($_POST['nomor_kk'] ?? '');
    $anggota  = $_POST['anggota'] ?? [];

    // Server-side KK validation (16 digits)
    if (!preg_match('/^\d{16}$/', $nomor_kk)) {
        $_SESSION['error_msg'] = "Nomor KK harus tepat 16 digit angka.";
        header("Location: keluarga_add.php"); exit;
    }
    if (empty($anggota)) {
        $_SESSION['error_msg'] = "Minimal satu anggota keluarga wajib diisi.";
        header("Location: keluarga_add.php"); exit;
    }

    // Auto-assign seksi
    $seksi_pria = null; $seksi_wanita = null;
    foreach ($seksis as $s) {
        if (stripos($s['nama_seksi'], 'pria') !== false || stripos($s['nama_seksi'], 'bapak') !== false) $seksi_pria  = $s['id_seksi'];
        if (stripos($s['nama_seksi'], 'wanita') !== false || stripos($s['nama_seksi'], 'ibu') !== false) $seksi_wanita = $s['id_seksi'];
    }

    try {
        $pdo->beginTransaction();

        $pdo->prepare("INSERT INTO tblKeluarga (nomor_kk) VALUES (?)")->execute([$nomor_kk]);
        $id_keluarga = (int) $pdo->lastInsertId();
        $id_kepala   = null;

        // Default sektor from head of family
        $sektor_kepala = null;
        foreach ($anggota as $a) {
            if (($a['status_dalam_keluarga'] ?? '') === 'Kepala Keluarga') {
                $sektor_kepala = $a['id_sektor'] ?: null;
                break;
            }
        }

        foreach ($anggota as $a) {
            $nama         = trim($a['nama_lengkap'] ?? '');
            $jk           = $a['jenis_kelamin'] ?? 'Laki-laki';
            $tgl_lahir    = $a['tanggal_lahir'] ?? '';
            $status_dlm   = $a['status_dalam_keluarga'] ?? 'Anak';
            $status_nikah = $a['status_pernikahan'] ?? 'Belum Menikah';
            $status_sidi  = $a['status_sidi'] ?? 'Tidak';
            $status_baptis= $a['status_baptis'] ?? 'Belum';
            $no_hp        = $a['no_hp'] ?? '';
            $email        = $a['email'] ?? '';
            $pekerjaan    = $a['pekerjaan'] ?? '';
            $alamat_ktp   = $a['alamat_ktp'] ?? '';
            $domisili_sama= ($a['domisili_sama'] ?? 'ya') === 'ya';
            $alamat_dom   = $domisili_sama ? $alamat_ktp : ($a['alamat_domisili'] ?? $alamat_ktp);
            $id_sektor    = !empty($a['id_sektor']) ? (int)$a['id_sektor'] : ($sektor_kepala ?? null);

            if (!$nama || !$tgl_lahir) continue;

            // Store alamat_ktp in alamat field; store domisili in a separate combined format
            $alamat_combined = "KTP: " . $alamat_ktp;
            if (!$domisili_sama) $alamat_combined .= " | Domisili: " . $alamat_dom;

            $pdo->prepare("INSERT INTO tblJemaat
                (id_keluarga, status_dalam_keluarga, nama_lengkap, jenis_kelamin, tanggal_lahir,
                 status_sidi, status_pernikahan, id_sektor, alamat, no_hp, alamat_email_aktif, status_keanggotaan)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,'Aktif')")
                ->execute([$id_keluarga, $status_dlm, $nama, $jk, $tgl_lahir,
                           $status_sidi, $status_nikah, $id_sektor, $alamat_combined, $no_hp, $email]);
            $id_jemaat = (int) $pdo->lastInsertId();

            if ($status_dlm === 'Kepala Keluarga' && !$id_kepala) $id_kepala = $id_jemaat;

            // Auto seksi
            $auto_seksi = null;
            if (in_array($status_dlm, ['Kepala Keluarga','Suami']) && $jk === 'Laki-laki') $auto_seksi = $seksi_pria;
            elseif ($status_dlm === 'Istri' && $jk === 'Perempuan') $auto_seksi = $seksi_wanita;
            elseif ($status_dlm === 'Anak' && !empty($a['id_seksi_anak'])) $auto_seksi = (int)$a['id_seksi_anak'];
            if ($auto_seksi) {
                $pdo->prepare("INSERT IGNORE INTO tblPengurusSeksi (id_jemaat,id_seksi,id_jabatan,status_approval) VALUES (?,?,1,'Approved')")
                    ->execute([$id_jemaat, $auto_seksi]);
            }

            // Keahlian
            if (!empty($a['keahlian']) && is_array($a['keahlian'])) {
                foreach ($a['keahlian'] as $kid) {
                    $pdo->prepare("INSERT IGNORE INTO tblKeahlian_Pelayan (id_jemaat,id_keahlian) VALUES (?,?)")->execute([$id_jemaat,(int)$kid]);
                }
            }
            // Peran Ibadah
            if (!empty($a['peran_ibadah']) && is_array($a['peran_ibadah'])) {
                foreach ($a['peran_ibadah'] as $pid) {
                    $pdo->prepare("INSERT IGNORE INTO tblPetugasIbadah (id_ibadah,id_jemaat,id_peran_ibadah) VALUES (0,?,?)")->execute([$id_jemaat,(int)$pid]);
                }
            }
        }

        if ($id_kepala) {
            $pdo->prepare("UPDATE tblKeluarga SET id_kepala_keluarga=? WHERE id_keluarga=?")->execute([$id_kepala,$id_keluarga]);
        }

        $pdo->commit();
        $_SESSION['success_msg'] = "Keluarga No. KK {$nomor_kk} berhasil didaftarkan.";
        header("Location: keluarga_detail.php?id={$id_keluarga}"); exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Terjadi kesalahan: " . $e->getMessage();
        header("Location: keluarga_add.php"); exit;
    }
}

// Build sektor options HTML for template
ob_start();
foreach ($sektors as $s) echo "<option value=\"{$s['id_sektor']}\">" . htmlspecialchars($s['nama_sektor']) . "</option>";
$sektor_opts = ob_get_clean();

ob_start();
foreach ($seksis as $s) echo "<option value=\"{$s['id_seksi']}\">" . htmlspecialchars($s['nama_seksi']) . "</option>";
$seksi_opts = ob_get_clean();

ob_start();
foreach ($keahlians as $k) {
    echo "<div class=\"form-check form-check-inline\">
        <input class=\"form-check-input\" type=\"checkbox\" name=\"anggota[__IDX__][keahlian][]\" value=\"{$k['id_keahlian']}\" id=\"khl_{$k['id_keahlian']}___IDX__\">
        <label class=\"form-check-label small\" for=\"khl_{$k['id_keahlian']}___IDX__\">" . htmlspecialchars($k['nama_keahlian']) . "</label>
    </div>";
}
$keahlian_opts = ob_get_clean();

ob_start();
foreach ($peran_ibadah as $p) {
    echo "<div class=\"form-check form-check-inline\">
        <input class=\"form-check-input\" type=\"checkbox\" name=\"anggota[__IDX__][peran_ibadah][]\" value=\"{$p['id_peran_ibadah']}\" id=\"pr_{$p['id_peran_ibadah']}___IDX__\">
        <label class=\"form-check-label small\" for=\"pr_{$p['id_peran_ibadah']}___IDX__\">" . htmlspecialchars($p['nama_peran']) . "</label>
    </div>";
}
$peran_opts = ob_get_clean();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="keluarga.php" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
        <h2 class="fw-bold mb-0"><i class="fas fa-home me-2 text-primary"></i>Daftarkan Keluarga Baru</h2>
        <p class="text-muted small mb-0">Entry satu Kartu Keluarga lengkap beserta seluruh anggota dalam sekali simpan.</p>
    </div>
</div>

<?php if (isset($_SESSION['error_msg'])): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<form method="POST" id="familyForm" novalidate>
    <?= csrf_field() ?>
<!-- ── Nomor KK ── -->
<div class="card-admin mb-4">
    <div class="card-header"><h5><i class="fas fa-id-card me-2 text-primary"></i>Data Kartu Keluarga</h5></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Nomor KK <span class="text-danger">*</span>
                    <span class="text-muted fw-normal">(Tepat 16 Digit)</span></label>
                <input type="text" name="nomor_kk" id="nomor_kk" class="form-control font-monospace"
                    required maxlength="16" minlength="16" pattern="\d{16}"
                    inputmode="numeric" placeholder="0000000000000000" autofocus
                    oninput="this.value=this.value.replace(/\D/g,'').slice(0,16); updateKKCounter(this);">
                <div class="d-flex justify-content-between mt-1">
                    <div class="invalid-feedback">Nomor KK harus tepat 16 digit angka.</div>
                    <small class="text-muted ms-auto"><span id="kk_count">0</span>/16 digit</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Anggota ── -->
<div class="card-admin mb-4">
    <div class="card-header">
        <h5><i class="fas fa-users me-2 text-primary"></i>Anggota Keluarga</h5>
        <button type="button" class="btn btn-sm btn-primary-custom" id="addMemberBtn">
            <i class="fas fa-user-plus me-1"></i>Tambah Anggota
        </button>
    </div>
    <div class="card-body p-0">
        <div id="memberList"></div>
    </div>
</div>

<div class="d-flex gap-3 mb-5">
    <button type="submit" class="btn btn-primary-custom btn-lg px-5">
        <i class="fas fa-save me-2"></i>Simpan Seluruh Data Keluarga
    </button>
    <a href="keluarga.php" class="btn btn-lg btn-outline-secondary">Batal</a>
</div>
</form>

<script>
// ── KK counter ──
function updateKKCounter(el) {
    document.getElementById('kk_count').textContent = el.value.length;
    el.classList.toggle('is-invalid', el.value.length > 0 && el.value.length !== 16);
    el.classList.toggle('is-valid',   el.value.length === 16);
}

// ── Member Template ──
const SEKTOR_OPTS   = `<option value="">-- Ikuti Kepala KK --</option><?= addslashes($sektor_opts) ?>`;
const SEKSI_OPTS    = `<option value="">-- Pilih Seksi --</option><?= addslashes($seksi_opts) ?>`;
const KEAHLIAN_OPTS = `<?= addslashes($keahlian_opts) ?>`;
const PERAN_OPTS    = `<?= addslashes($peran_opts) ?>`;

let memberCount = 0;
const memberList = document.getElementById('memberList');

function buildMemberHTML(idx, num) {
    return `
<div class="member-card border-top p-3 p-md-4" data-index="${idx}" style="background:${idx%2===0?'#fafbff':'#fff'}">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-user-circle me-2"></i>Anggota ke-<span class="member-number">${num}</span></h6>
        <button type="button" class="btn btn-sm btn-outline-danger remove-member"><i class="fas fa-user-minus me-1"></i>Hapus</button>
    </div>

    <!-- Row 1: Identitas Utama -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="anggota[${idx}][nama_lengkap]" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Status dalam KK <span class="text-danger">*</span></label>
            <select name="anggota[${idx}][status_dalam_keluarga]" class="form-select status-select" required>
                <option value="Kepala Keluarga">Kepala Keluarga</option>
                <option value="Suami">Suami</option>
                <option value="Istri">Istri</option>
                <option value="Anak">Anak</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
            <select name="anggota[${idx}][jenis_kelamin]" class="form-select jk-select" required>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>
        </div>
    </div>

    <!-- Row 2: Data Lahir & Status -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
            <input type="date" name="anggota[${idx}][tanggal_lahir]" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Status Pernikahan <span class="text-danger">*</span></label>
            <select name="anggota[${idx}][status_pernikahan]" class="form-select status-nikah-select" required>
                <option value="Belum Menikah">Belum Menikah</option>
                <option value="Sudah Menikah" selected>Sudah Menikah</option>
                <option value="Cerai Hidup">Cerai Hidup</option>
                <option value="Cerai Mati">Cerai Mati</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Status Baptis</label>
            <select name="anggota[${idx}][status_baptis]" class="form-select">
                <option value="Sudah">Sudah Dibaptis</option>
                <option value="Belum" selected>Belum Dibaptis</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Sudah Sidi?</label>
            <select name="anggota[${idx}][status_sidi]" class="form-select">
                <option value="Tidak" selected>Tidak</option>
                <option value="Ya">Ya</option>
            </select>
        </div>
    </div>

    <!-- Row 3: Kontak -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">No. HP <span class="text-danger">*</span>
                <span class="text-muted fw-normal">(7-15 digit)</span></label>
            <input type="tel" name="anggota[${idx}][no_hp]" class="form-control no-hp-input"
                required maxlength="15" minlength="7" inputmode="numeric"
                placeholder="08xxxxxxxxxx"
                oninput="this.value=this.value.replace(/[^0-9+\-\s]/g,'').slice(0,15);">
            <div class="invalid-feedback">No. HP wajib diisi (7-15 digit).</div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="anggota[${idx}][email]" class="form-control"
                placeholder="contoh@email.com"
                oninput="this.classList.toggle('is-invalid', this.value.length > 0 && !this.validity.valid);">
            <div class="invalid-feedback">Format email tidak valid.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Pekerjaan</label>
            <input type="text" name="anggota[${idx}][pekerjaan]" class="form-control" placeholder="Guru, Wiraswasta, dll.">
        </div>
    </div>

    <!-- Row 4: Alamat -->
    <div class="row g-3 mb-3">
        <div class="col-12">
            <label class="form-label fw-semibold">Alamat KTP <span class="text-danger">*</span></label>
            <textarea name="anggota[${idx}][alamat_ktp]" class="form-control alamat-ktp-input" rows="2" required
                placeholder="Alamat sesuai Kartu Tanda Penduduk..."></textarea>
        </div>
        <div class="col-12">
            <div class="form-check mb-2">
                <input class="form-check-input domisili-sama-check" type="checkbox" 
                    id="domisili_sama_${idx}" name="anggota[${idx}][domisili_sama]" value="ya" checked>
                <label class="form-check-label fw-semibold" for="domisili_sama_${idx}">
                    <i class="fas fa-check-circle text-success me-1"></i>Alamat Domisili sama dengan Alamat KTP
                </label>
            </div>
            <div class="domisili-custom-wrap" style="display:none;">
                <label class="form-label fw-semibold">Alamat Domisili <span class="text-danger">*</span></label>
                <textarea name="anggota[${idx}][alamat_domisili]" class="form-control" rows="2"
                    placeholder="Alamat tempat tinggal saat ini..."></textarea>
            </div>
        </div>
    </div>

    <!-- Row 5: Organisasi -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Sektor</label>
            <select name="anggota[${idx}][id_sektor]" class="form-select">${SEKTOR_OPTS}</select>
        </div>
        <div class="col-md-4 seksi-anak-wrapper" style="display:none;">
            <label class="form-label fw-semibold">Seksi (Anak/Remaja) <span class="text-danger">*</span></label>
            <select name="anggota[${idx}][id_seksi_anak]" class="form-select">${SEKSI_OPTS}</select>
        </div>
    </div>

    ${KEAHLIAN_OPTS ? `<div class="mb-3">
        <label class="form-label fw-semibold">Keahlian</label>
        <div class="d-flex flex-wrap gap-2">${KEAHLIAN_OPTS.replaceAll('__IDX__', idx)}</div>
    </div>` : ''}

    ${PERAN_OPTS ? `<div class="mb-2">
        <label class="form-label fw-semibold">Peran Ibadah</label>
        <div class="d-flex flex-wrap gap-2">${PERAN_OPTS.replaceAll('__IDX__', idx)}</div>
    </div>` : ''}
</div>`;
}

function addMember() {
    const idx = memberCount++;
    const num = memberList.children.length + 1;
    const wrap = document.createElement('div');
    wrap.innerHTML = buildMemberHTML(idx, num);
    const el = wrap.firstElementChild;
    memberList.appendChild(el);
    initMember(el);
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function initMember(el) {
    const statusSel  = el.querySelector('.status-select');
    const nikahSel   = el.querySelector('.status-nikah-select');
    const seksiWrap  = el.querySelector('.seksi-anak-wrapper');
    const jkSel      = el.querySelector('.jk-select');
    const alamatKTP  = el.querySelector('.alamat-ktp-input');
    const domSameChk = el.querySelector('.domisili-sama-check');
    const domWrap    = el.querySelector('.domisili-custom-wrap');
    const domArea    = domWrap ? domWrap.querySelector('textarea') : null;

    // Domisili toggle
    function syncDomisili() {
        const same = domSameChk.checked;
        domWrap.style.display = same ? 'none' : '';
        if (domArea) domArea.required = !same;
    }
    domSameChk.addEventListener('change', syncDomisili);

    // If user types in alamat domisili, uncheck the checkbox
    if (domArea) {
        domArea.addEventListener('input', function() {
            if (this.value.trim()) domSameChk.checked = false;
            syncDomisili();
        });
    }

    // If user types in KTP area and domisili is "same", keep it in sync (visual cue)
    if (alamatKTP) {
        alamatKTP.addEventListener('input', function() {
            // Nothing to sync in DOM — PHP handles this on submit
        });
    }

    // Status change → auto gender + seksi + nikah
    function onStatusChange() {
        const val = statusSel.value;
        if (val === 'Anak') {
            nikahSel.value = 'Belum Menikah';
            seksiWrap.style.display = '';
        } else {
            seksiWrap.style.display = 'none';
        }
        if (val === 'Istri') jkSel.value = 'Perempuan';
        else if (val === 'Suami' || val === 'Kepala Keluarga') jkSel.value = 'Laki-laki';
    }
    statusSel.addEventListener('change', onStatusChange);
    onStatusChange();
    syncDomisili();

    // Remove button
    el.querySelector('.remove-member').addEventListener('click', function() {
        if (memberList.children.length > 1) {
            el.remove();
            // Renumber remaining
            memberList.querySelectorAll('.member-number').forEach((span, i) => span.textContent = i + 1);
        } else {
            alert('Minimal satu anggota keluarga harus diisi.');
        }
    });

    // HP validation display
    const hpInput = el.querySelector('.no-hp-input');
    if (hpInput) {
        hpInput.addEventListener('input', function() {
            const digits = this.value.replace(/\D/g,'');
            const ok = digits.length >= 7 && digits.length <= 15;
            this.classList.toggle('is-invalid', this.value.length > 0 && !ok);
            this.classList.toggle('is-valid',   ok);
        });
    }
}

// Form validation before submit
document.getElementById('familyForm').addEventListener('submit', function(e) {
    let valid = true;
    // KK validation
    const kkInput = document.getElementById('nomor_kk');
    if (!/^\d{16}$/.test(kkInput.value)) {
        kkInput.classList.add('is-invalid'); valid = false;
    }
    // HP validation per member
    document.querySelectorAll('.no-hp-input').forEach(function(hp) {
        const digits = hp.value.replace(/\D/g,'');
        if (digits.length < 7) {
            hp.classList.add('is-invalid'); valid = false;
        }
    });
    if (!valid) {
        e.preventDefault();
        document.querySelector('.is-invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// Auto-add first member
addMember();
document.getElementById('addMemberBtn').addEventListener('click', addMember);
</script>

<?php require_once '../../includes/admin_footer.php'; ?>
