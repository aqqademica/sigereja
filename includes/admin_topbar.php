<?php
$notif_count = $notif_count ?? 0;
$notif_list  = $notif_list ?? [];
$page_titles = [
    'dashboard.php'          => 'Dashboard',
    'jemaat.php'             => 'Data Jemaat',
    'jemaat_view.php'        => 'Detail Jemaat',
    'jemaat_edit.php'        => 'Edit Jemaat',
    'keluarga.php'           => 'Data Keluarga',
    'keluarga_detail.php'    => 'Detail Keluarga',
    'keluarga_add.php'       => 'Tambah Keluarga Baru',
    'pengurus.php'           => 'Kepengurusan',
    'mutasi.php'             => 'Mutasi Jemaat',
    'jadwal.php'             => 'Jadwal & Liturgi',
    'jadwal_detail.php'      => 'Detail Jadwal',
    'kegiatan.php'           => 'Kegiatan',
    'pendaftaran.php'        => 'Pendaftaran Sakramen',
    'warta.php'              => 'Warta Jemaat',
    'users.php'              => 'Manajemen Akun',
    'master_sektor.php'      => 'Master Sektor',
    'master_seksi.php'       => 'Master Seksi',
    'master_jabatan.php'     => 'Master Jabatan',
    'master_keahlian.php'    => 'Master Keahlian',
    'master_peran_ibadah.php'=> 'Master Peran Ibadah',
];
$current_page = basename($_SERVER['PHP_SELF']);
$page_title   = $page_titles[$current_page] ?? 'Admin Panel';
?>
<header class="topbar">
    <div class="topbar-left">
        <div>
            <div class="topbar-title"><?= htmlspecialchars($page_title) ?></div>
            <div class="topbar-breadcrumb">
                <i class="fas fa-church me-1"></i> SIGereja &rsaquo; <?= htmlspecialchars($page_title) ?>
            </div>
        </div>
    </div>
    <div class="topbar-right">
        <!-- Notification Bell -->
        <div class="dropdown notif-wrapper">
            <button class="btn btn-sm btn-light border-0 position-relative" data-bs-toggle="dropdown" id="notifBell" aria-expanded="false" style="width:38px;height:38px;border-radius:50%;padding:0;">
                <i class="fas fa-bell text-secondary"></i>
                <?php if ($notif_count > 0): ?>
                <span class="notif-badge"><?= $notif_count > 9 ? '9+' : $notif_count ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end notif-dropdown-admin p-0" aria-labelledby="notifBell">
                <div class="notif-header">
                    <span><i class="fas fa-bell me-2"></i>Notifikasi</span>
                    <?php if ($notif_count > 0): ?>
                    <a href="#" class="text-white small" id="markAllRead" style="opacity:.7">Tandai semua dibaca</a>
                    <?php endif; ?>
                </div>
                <div style="max-height:320px;overflow-y:auto;">
                    <?php if (empty($notif_list)): ?>
                    <div class="text-center text-muted py-4 small">Tidak ada notifikasi baru.</div>
                    <?php else: foreach ($notif_list as $n): ?>
                    <a href="<?= htmlspecialchars($n['url'] ?: '#') ?>" class="text-decoration-none">
                        <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                            <div class="notif-title"><?= htmlspecialchars($n['judul']) ?></div>
                            <div class="notif-msg"><?= htmlspecialchars(mb_strimwidth($n['pesan'], 0, 80, '…')) ?></div>
                            <div class="notif-time"><i class="far fa-clock me-1"></i><?= date('d M, H:i', strtotime($n['created_at'])) ?></div>
                        </div>
                    </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="dropdown">
            <button class="btn btn-sm btn-light border d-flex align-items-center gap-2" data-bs-toggle="dropdown" style="border-radius:20px;padding:0.3rem 0.9rem 0.3rem 0.3rem;">
                <div style="width:30px;height:30px;background:var(--alt-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.8rem;font-weight:700;">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                </div>
                <span class="small fw-600 d-none d-md-inline"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
                <i class="fas fa-chevron-down small text-muted"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:10px;border-color:#e5e7f0;">
                <li><span class="dropdown-item-text small text-muted"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></span></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item small" href="../../index.php"><i class="fas fa-globe me-2 text-primary"></i>Portal Jemaat</a></li>
                <li><a class="dropdown-item small text-danger" href="../../actions/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
            </ul>
        </div>
    </div>
</header>

<script>
document.getElementById('markAllRead')?.addEventListener('click', function(e) {
    e.preventDefault();
    fetch('../../actions/mark_notif_read.php')
        .then(() => { location.reload(); });
});
</script>
