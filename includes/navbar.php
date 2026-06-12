<?php
$notif_count = $notif_count ?? 0;
$notif_list  = $notif_list  ?? [];
$current = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <div style="width:34px;height:34px;background:linear-gradient(135deg,hsl(246,80%,60%),hsl(246,80%,40%));border-radius:8px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(79,70,229,.4);">
                <i class="fas fa-church text-white" style="font-size:.9rem;"></i>
            </div>
            <span style="color:#fff;font-weight:800;font-size:1.2rem;letter-spacing:-.5px;">SI<span style="color:hsl(35,90%,58%);">Gereja</span></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <i class="fas fa-bars text-white"></i>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto ms-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $current==='index.php' ? 'active' : '' ?>" href="index.php">
                        <i class="fas fa-home me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current==='warta.php' ? 'active' : '' ?>" href="warta.php">
                        <i class="fas fa-newspaper me-1"></i>Warta
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="pages/jemaat/jadwal.php">
                        <i class="fas fa-calendar-alt me-1"></i>Jadwal
                    </a>
                </li>
                <?php if (isset($_SESSION['id_jemaat'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="pages/jemaat/pendaftaran.php">
                        <i class="fas fa-hand-holding-heart me-1"></i>Daftar Sakramen
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>

            <div class="navbar-nav d-flex align-items-center gap-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Notification Bell -->
                    <div class="dropdown notif-wrapper">
                        <button class="btn btn-sm border-0 position-relative" data-bs-toggle="dropdown" style="color:rgba(255,255,255,.8);width:36px;height:36px;border-radius:50%;padding:0;background:rgba(255,255,255,.1);">
                            <i class="fas fa-bell"></i>
                            <?php if ($notif_count > 0): ?>
                            <span class="notif-badge"><?= $notif_count > 9 ? '9+' : $notif_count ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0">
                            <div style="padding:.75rem 1rem;background:rgba(20,16,60,.95);border-radius:20px 20px 0 0;display:flex;justify-content:space-between;align-items:center;">
                                <span style="color:#fff;font-weight:700;font-size:.85rem;"><i class="fas fa-bell me-2"></i>Notifikasi</span>
                                <?php if ($notif_count > 0): ?>
                                <a href="#" class="text-white small mark-read-link" style="opacity:.7">Baca semua</a>
                                <?php endif; ?>
                            </div>
                            <div style="max-height:280px;overflow-y:auto;">
                                <?php if (empty($notif_list)): ?>
                                    <div class="text-center py-4 small" style="color:rgba(255,255,255,.5);">Tidak ada notifikasi.</div>
                                <?php else: foreach ($notif_list as $n): ?>
                                    <a href="<?= htmlspecialchars($n['url'] ?: '#') ?>" class="text-decoration-none">
                                        <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                                            <div class="notif-title"><?= htmlspecialchars($n['judul']) ?></div>
                                            <div class="notif-msg"><?= htmlspecialchars(mb_strimwidth($n['pesan'],0,75,'…')) ?></div>
                                            <div class="notif-time"><?= date('d M, H:i', strtotime($n['created_at'])) ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown"
                          style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:20px;color:#fff;padding:.35rem .9rem .35rem .4rem;">
                            <div style="width:28px;height:28px;background:hsl(246,80%,60%);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;">
                                <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                            </div>
                            <span class="small fw-semibold d-none d-md-inline"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
                            <i class="fas fa-chevron-down" style="font-size:.65rem;opacity:.6;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" style="border-radius:12px;border:1px solid rgba(255,255,255,.15);background:rgba(20,16,60,.97);backdrop-filter:blur(20px);">
                            <li>
                                <span class="dropdown-item-text" style="color:rgba(255,255,255,.5);font-size:.75rem;">
                                    <?= htmlspecialchars($_SESSION['role'] ?? '') ?>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,.1);"></li>
                            <?php if (in_array($_SESSION['role'] ?? '', ['Super Admin','Admin Sistem','Sekretaris','Ketua Sektor','Pendeta'])): ?>
                            <li><a class="dropdown-item" href="pages/admin/dashboard.php" style="color:rgba(255,255,255,.8);font-size:.85rem;"><i class="fas fa-shield-alt me-2 text-primary"></i>Panel Admin</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="actions/logout.php" style="color:#f87171;font-size:.85rem;"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm" style="background:rgba(255, 255, 255, 0.59);border:1px solid rgba(255, 255, 255, 0.2);color:rgb(57, 64, 190);border-radius:20px;font-weight:500;">
                        <i class="fas fa-sign-in-alt me-1"></i>Masuk
                    </a>
                    <a href="register.php" class="btn btn-sm btn-primary-custom">
                        <i class="fas fa-user-plus me-1"></i>Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<div style="height:64px;"></div><!-- Navbar spacer -->
