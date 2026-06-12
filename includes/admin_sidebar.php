<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
$is_super = in_array($role, ['Super Admin', 'Admin Sistem']);
$is_sekretaris = in_array($role, ['Super Admin', 'Admin Sistem', 'Sekretaris']);
?>
<aside class="sidebar">
    <!-- Brand -->
    <a href="dashboard.php" class="sidebar-brand text-decoration-none">
        <div class="brand-icon"><i class="fas fa-church"></i></div>
        <div>
            <div class="brand-name">SIGereja</div>
            <div class="brand-sub">Sistem Informasi Gereja</div>
        </div>
    </a>

    <!-- Nav -->
    <ul class="sidebar-nav">
        <!-- Dashboard -->
        <li><a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span> Dashboard
        </a></li>

        <?php if ($is_sekretaris): ?>
        <!-- Master Data -->
        <li class="nav-section-title">Master Data</li>
        <li><a href="master_sektor.php" class="<?= $current_page === 'master_sektor.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-map-marker-alt"></i></span> Sektor
        </a></li>
        <li><a href="master_seksi.php" class="<?= $current_page === 'master_seksi.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-layer-group"></i></span> Seksi
        </a></li>
        <li><a href="master_jabatan.php" class="<?= $current_page === 'master_jabatan.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-briefcase"></i></span> Jabatan
        </a></li>
        <li><a href="master_keahlian.php" class="<?= $current_page === 'master_keahlian.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-star"></i></span> Keahlian
        </a></li>
        <li><a href="master_peran_ibadah.php" class="<?= $current_page === 'master_peran_ibadah.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-music"></i></span> Peran Ibadah
        </a></li>
        <?php if ($is_super): ?>
        <li><a href="users.php" class="<?= $current_page === 'users.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-user-shield"></i></span> Manajemen Akun
        </a></li>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Data Jemaat -->
        <li class="nav-section-title">Data Jemaat</li>
        <li><a href="keluarga.php" class="<?= in_array($current_page, ['keluarga.php','keluarga_detail.php','keluarga_add.php']) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-home"></i></span> Data Keluarga
        </a></li>
        <li><a href="jemaat.php" class="<?= in_array($current_page, ['jemaat.php','jemaat_view.php','jemaat_edit.php']) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-users"></i></span> Data Jemaat
        </a></li>
        <li><a href="pengurus.php" class="<?= $current_page === 'pengurus.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-user-tie"></i></span> Kepengurusan
        </a></li>
        <li><a href="mutasi.php" class="<?= $current_page === 'mutasi.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span> Mutasi Jemaat
        </a></li>

        <!-- Pelayanan -->
        <li class="nav-section-title">Pelayanan</li>
        <li><a href="jadwal.php" class="<?= in_array($current_page, ['jadwal.php','jadwal_detail.php']) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span> Jadwal & Liturgi
        </a></li>
        <li><a href="kegiatan.php" class="<?= $current_page === 'kegiatan.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-calendar-check"></i></span> Kegiatan
        </a></li>
        <li><a href="pendaftaran.php" class="<?= $current_page === 'pendaftaran.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-hand-holding-heart"></i></span> Pendaftaran Sakramen
        </a></li>

        <!-- Warta -->
        <li class="nav-section-title">Publikasi</li>
        <li><a href="warta.php" class="<?= $current_page === 'warta.php' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-newspaper"></i></span> Warta Jemaat
        </a></li>
    </ul>

    <!-- Footer links -->
    <div class="sidebar-footer">
        <a href="../../index.php"><i class="fas fa-globe fa-fw"></i> Lihat Portal Jemaat</a>
        <a href="../../actions/logout.php" class="mt-1"><i class="fas fa-sign-out-alt fa-fw"></i> Keluar</a>
    </div>
</aside>
