<?php
require_once '../../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('mutasi.php'); // H5
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ajukan_mutasi') {
        $id_pengaju = $_POST['id_jemaat_pengaju'] ?? '';
        $gereja_tujuan = trim($_POST['gereja_tujuan'] ?? '');
        $alasan = trim($_POST['alasan_mutasi'] ?? '');
        $anggota_mutasi = $_POST['anggota_mutasi'] ?? []; // Array of id_jemaat
        
        if ($id_pengaju && $gereja_tujuan && !empty($anggota_mutasi)) {
            $pdo->beginTransaction();
            try {
                $tanggal = date('Y-m-d');
                $stmt = $pdo->prepare("INSERT INTO tblMutasi (id_jemaat_pengaju, tanggal_pengajuan, gereja_tujuan, alasan_mutasi, status_approval) VALUES (?, ?, ?, ?, 'Pending')");
                $stmt->execute([$id_pengaju, $tanggal, $gereja_tujuan, $alasan]);
                $id_mutasi = $pdo->lastInsertId();
                
                $stmt_det = $pdo->prepare("INSERT INTO tblMutasi_Detail (id_mutasi, id_jemaat) VALUES (?, ?)");
                foreach ($anggota_mutasi as $id_jem) {
                    $stmt_det->execute([$id_mutasi, $id_jem]);
                }
                
                $pdo->commit();
                $_SESSION['success_msg'] = "Pengajuan mutasi berhasil dibuat dan menunggu persetujuan.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error_msg'] = "Terjadi kesalahan: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_msg'] = "Mohon lengkapi form dan pilih minimal 1 jemaat yang akan dimutasi.";
        }
    } elseif ($action === 'approve') {
        $id_mutasi = $_POST['id_mutasi'] ?? '';
        if ($id_mutasi) {
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE tblMutasi SET status_approval = 'Approved' WHERE id_mutasi = ?")->execute([$id_mutasi]);
                
                // Get all members in this mutasi
                $details = $pdo->prepare("SELECT id_jemaat FROM tblMutasi_Detail WHERE id_mutasi = ?");
                $details->execute([$id_mutasi]);
                $members = $details->fetchAll(PDO::FETCH_COLUMN);
                
                // Get reason and target church to log into tblJemaat
                $mutasi_info = $pdo->prepare("SELECT gereja_tujuan, alasan_mutasi FROM tblMutasi WHERE id_mutasi = ?");
                $mutasi_info->execute([$id_mutasi]);
                $info = $mutasi_info->fetch();
                $alasan_pindah = "Pindah ke " . $info['gereja_tujuan'] . " - Alasan: " . $info['alasan_mutasi'];
                
                if (!empty($members)) {
                    $in = str_repeat('?,', count($members) - 1) . '?';
                    $params = array_merge([$alasan_pindah], $members);
                    $pdo->prepare("UPDATE tblJemaat SET status_keanggotaan = 'Pindah Gereja (Mutasi)', alasan_pindah = ? WHERE id_jemaat IN ($in)")->execute($params);
                }
                
                $pdo->commit();
                $_SESSION['success_msg'] = "Pengajuan mutasi disetujui. Status keanggotaan jemaat telah diubah.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error_msg'] = "Gagal memproses persetujuan.";
            }
        }
    } elseif ($action === 'reject') {
        // M5 fix: Add reject capability
        $id_mutasi = (int) ($_POST['id_mutasi'] ?? 0);
        if ($id_mutasi) {
            $pdo->prepare("UPDATE tblMutasi SET status_approval = 'Ditolak' WHERE id_mutasi = ? AND status_approval = 'Pending'")
                ->execute([$id_mutasi]);
            $_SESSION['success_msg'] = "Pengajuan mutasi telah ditolak.";
        }
    }
    header("Location: mutasi.php");
    exit;
}

// Fetch lists
$list_keluarga = $pdo->query("SELECT * FROM tblKeluarga ORDER BY nomor_kk")->fetchAll();
$mutasis = $pdo->query("
    SELECT m.*, j.nama_lengkap as pengaju, 
    (SELECT COUNT(*) FROM tblMutasi_Detail WHERE id_mutasi = m.id_mutasi) as jumlah_orang 
    FROM tblMutasi m 
    JOIN tblJemaat j ON m.id_jemaat_pengaju = j.id_jemaat 
    ORDER BY m.tanggal_pengajuan DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Manajemen Mutasi / Pindah Gereja</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-sign-out-alt me-2"></i>Ajukan Mutasi</button>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="glass-card-admin">
    <h5 class="fw-bold mb-3">Daftar Riwayat Mutasi</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Pengaju</th>
                    <th>Jml Orang</th>
                    <th>Gereja Tujuan</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($mutasis)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Belum ada data mutasi.</td></tr>
                <?php endif; ?>
                <?php foreach($mutasis as $m): ?>
                <tr>
                    <td><?= date('d M Y', strtotime($m['tanggal_pengajuan'])) ?></td>
                    <td><?= htmlspecialchars($m['pengaju']) ?></td>
                    <td><?= $m['jumlah_orang'] ?> Jemaat</td>
                    <td><?= htmlspecialchars($m['gereja_tujuan']) ?></td>
                    <td>
                        <?php if($m['status_approval'] == 'Approved'): ?>
                            <span class="badge bg-success">Disetujui</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-info text-white btn-detail-mutasi"
                            data-alasan="<?= htmlspecialchars($m['alasan_mutasi'], ENT_QUOTES) ?>"
                            data-gereja="<?= htmlspecialchars($m['gereja_tujuan'], ENT_QUOTES) ?>"
                            data-pengaju="<?= htmlspecialchars($m['pengaju'], ENT_QUOTES) ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if($m['status_approval'] == 'Pending'): ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Setujui mutasi ini? Anggota terkait akan otomatis berubah status menjadi Pindah Gereja.');">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="approve">
                            <input type="hidden" name="id_mutasi" value="<?= $m['id_mutasi'] ?>">
                            <button class="btn btn-sm btn-success" title="Setujui"><i class="fas fa-check"></i></button>
                        </form>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Tolak pengajuan mutasi ini?');">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="reject">
                            <input type="hidden" name="id_mutasi" value="<?= $m['id_mutasi'] ?>">
                            <button class="btn btn-sm btn-outline-danger" title="Tolak"><i class="fas fa-times"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pengajuan Mutasi -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Ajukan Mutasi (Pindah Gereja)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
    <?= csrf_field() ?>
<div class="modal-body">
                    <input type="hidden" name="action" value="ajukan_mutasi">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Keluarga (Cari berdasarkan KK)</label>
                            <select id="keluarga_select" class="form-select" onchange="fetchAnggota(this.value)">
                                <option value="">-- Pilih Keluarga --</option>
                                <?php foreach($list_keluarga as $k): ?>
                                    <option value="<?= $k['id_keluarga'] ?>"><?= htmlspecialchars($k['nomor_kk']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div id="anggota_container" style="display:none;" class="mb-3 border p-3 rounded bg-light">
                        <h6 class="fw-bold">Pilih Anggota yang akan Mutasi:</h6>
                        <div id="anggota_list"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gereja Tujuan</label>
                        <input type="text" name="gereja_tujuan" class="form-control" required placeholder="Contoh: HKBP Menteng">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Kepindahan</label>
                        <textarea name="alasan_mutasi" class="form-control" required rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom">Ajukan Mutasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function fetchAnggota(id_keluarga) {
    if (!id_keluarga) {
        document.getElementById('anggota_container').style.display = 'none';
        return;
    }
    
    // Gunakan AJAX sederhana untuk mengambil data anggota keluarga
    fetch('../../actions/get_anggota.php?id_keluarga=' + id_keluarga)
    .then(response => response.json())
    .then(data => {
        let html = '';
        if(data.length > 0) {
            // Dropdown untuk Pengaju (biasanya kepala keluarga)
            html += '<div class="mb-2"><label class="form-label text-muted small">Jemaat Pengaju (Wakil):</label><select name="id_jemaat_pengaju" class="form-select form-select-sm mb-3" required>';
            data.forEach(j => { html += `<option value="${j.id_jemaat}">${j.nama_lengkap}</option>`; });
            html += '</select></div>';
            
            // Checkboxes
            data.forEach(j => {
                html += `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="anggota_mutasi[]" value="${j.id_jemaat}" id="cek${j.id_jemaat}" checked>
                    <label class="form-check-label" for="cek${j.id_jemaat}">
                        ${j.nama_lengkap} (${j.status_dalam_keluarga || 'Anggota'})
                    </label>
                </div>`;
            });
            document.getElementById('anggota_container').style.display = 'block';
        } else {
            html = '<p class="text-danger">Keluarga ini belum memiliki anggota jemaat yang terdaftar.</p>';
            document.getElementById('anggota_container').style.display = 'block';
        }
        document.getElementById('anggota_list').innerHTML = html;
    });
}
</script>

<!-- M9: Detail Mutasi Modal (replaces unsafe alert()) -->
<div class="modal fade" id="detailMutasiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detail Mutasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Nama Pengaju:</strong> <span id="detailPengaju"></span></p>
                <p><strong>Gereja Tujuan:</strong> <span id="detailGereja"></span></p>
                <p class="mb-1"><strong>Alasan Kepindahan:</strong></p>
                <p id="detailAlasan" class="text-muted" style="white-space:pre-wrap;"></p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-detail-mutasi').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('detailPengaju').textContent = this.dataset.pengaju;
        document.getElementById('detailGereja').textContent  = this.dataset.gereja;
        document.getElementById('detailAlasan').textContent  = this.dataset.alasan;
        new bootstrap.Modal(document.getElementById('detailMutasiModal')).show();
    });
});
</script>

<?php require_once '../../includes/admin_footer.php'; ?>
