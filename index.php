<?php require_once 'includes/header.php'; ?>

<div class="container py-5 fade-in-up">
    <!-- Hero -->
    <div class="row justify-content-center align-items-center mb-5 g-4">
        <div class="col-lg-8 col-md-10 text-center">
            <p class="mb-2" style="color:hsl(35,90%,58%);font-weight:600;font-size:.85rem;letter-spacing:.1em;text-transform:uppercase;">
                <i class="fas fa-church me-2"></i>Sistem Informasi Gereja Digital
            </p>
            <h1 class="hero-title display-5 fw-800 mb-3">
                Shalom! Selamat Datang di<br>
                <span style="color:hsl(35,90%,58%);">SI GKKD ABC  </span>
            </h1>
            <p class="hero-subtitle mb-4 mx-auto" style="font-size:1.05rem;line-height:1.7; max-width: 600px;">
                Sistem Informasi Gereja Digital — Jadwal Ibadah, Warta Jemaat, Pendaftaran Sakramen, dan Manajemen Data Jemaat
            </p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="d-flex gap-3 flex-wrap justify-content-center">
                    <a href="register.php" class="btn btn-accent-custom btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </a>
                    <a href="login.php" class="btn btn-lg btn-outline-primary" style="border-radius:12px; font-weight:600; padding: 0.65rem 1.6rem; border: 2px solid var(--primary); color: var(--primary);">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </a>
                </div>
            <?php else: ?>
                <div class="glass-card p-3 mx-auto" style="max-width: 450px;">
                    <div class="d-flex align-items-center gap-3 justify-content-center">
                        <div style="width:46px;height:46px;background:hsl(246,80%,60%);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff;font-weight:800;">
                            <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div class="text-start">
                            <div style="font-weight:700; color: var(--text-main);">Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</div>
                            <div class="text-muted" style="font-size:.82rem;"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></div>
                        </div>
                        <?php if (in_array($_SESSION['role'] ?? '', ['Super Admin','Admin Sistem','Sekretaris','Ketua Sektor','Pendeta'])): ?>
                        <a href="pages/admin/dashboard.php" class="btn btn-sm btn-primary-custom ms-auto">
                            <i class="fas fa-shield-alt me-1"></i>Panel Admin
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!-- <div class="col-lg-6 text-center">
            <div class="glass-card p-4" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,.15);">
                <i class="fas fa-cross" style="font-size:5rem;color:rgba(255,220,130,.7);margin-bottom:1rem;display:block;text-shadow:0 4px 20px rgba(245,158,11,.3);"></i>
                <h4 style="color:#fff;font-weight:700;margin-bottom:.5rem;">Pelayanan Digital</h4>
                <p style="color:rgba(255,255,255,.6);font-size:.9rem;margin:0;">Mendekatkan pelayanan kepada seluruh jemaat dengan teknologi modern.</p>
            </div>
        </div> -->
    </div>

    <!-- Feature Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4 fade-in-up card-delay-1">
            <a href="warta.php" class="text-decoration-none d-block h-100">
                <div class="glass-card h-100 text-center" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,.12);">
                    <div class="mb-3">
                        <div style="width:60px;height:60px;background:linear-gradient(135deg,hsl(246,80%,60%),hsl(246,80%,40%));border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto;box-shadow:0 8px 24px rgba(79,70,229,.35);">
                            <i class="fas fa-newspaper" style="font-size:1.4rem;color:#fff;"></i>
                        </div>
                    </div>
                    <h5 class="fw-700 mb-2" style="color:#fff;">Warta Jemaat</h5>
                    <p style="color:rgba(255,255,255,.6);font-size:.88rem;margin:0;">Baca warta dan pengumuman gereja terkini kapan saja dan di mana saja.</p>
                </div>
            </a>
        </div>
        <div class="col-md-4 fade-in-up card-delay-2">
            <a href="<?= isset($_SESSION['user_id']) ? 'pages/jemaat/jadwal.php' : 'login.php' ?>" class="text-decoration-none d-block h-100">
                <div class="glass-card h-100 text-center" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,.12);">
                    <div class="mb-3">
                        <div style="width:60px;height:60px;background:linear-gradient(135deg,hsl(151,60%,42%),hsl(151,60%,28%));border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto;box-shadow:0 8px 24px rgba(16,185,129,.35);">
                            <i class="fas fa-calendar-alt" style="font-size:1.4rem;color:#fff;"></i>
                        </div>
                    </div>
                    <h5 class="fw-700 mb-2" style="color:#fff;">Jadwal Ibadah</h5>
                    <p style="color:rgba(255,255,255,.6);font-size:.88rem;margin:0;">Pantau jadwal ibadah, liturgi, dan kegiatan gereja secara real-time.</p>
                </div>
            </a>
        </div>
        <div class="col-md-4 fade-in-up card-delay-3">
            <a href="<?= isset($_SESSION['user_id']) ? 'pages/jemaat/pendaftaran.php' : 'login.php' ?>" class="text-decoration-none d-block h-100">
                <div class="glass-card h-100 text-center" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,.12);">
                    <div class="mb-3">
                        <div style="width:60px;height:60px;background:linear-gradient(135deg,hsl(35,90%,55%),hsl(25,90%,45%));border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto;box-shadow:0 8px 24px rgba(245,158,11,.35);">
                            <i class="fas fa-hand-holding-heart" style="font-size:1.4rem;color:#fff;"></i>
                        </div>
                    </div>
                    <h5 class="fw-700 mb-2" style="color:#fff;">Daftar Sakramen</h5>
                    <p style="color:rgba(255,255,255,.6);font-size:.88rem;margin:0;">Daftar Baptisan, Sidi, dan Pernikahan langsung melalui portal digital.</p>
                </div>
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
