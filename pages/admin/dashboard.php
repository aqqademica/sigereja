<?php require_once '../../includes/admin_header.php'; ?>

<h2 class="mb-4 fw-bold">Dashboard</h2>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="glass-card-admin border-start border-primary border-4 py-2">
            <div class="row align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Jemaat</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM tblJemaat WHERE status_keanggotaan = 'Aktif'");
                            echo $stmt->fetchColumn();
                        ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-users fa-2x text-gray-300" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="glass-card-admin border-start border-success border-4 py-2">
            <div class="row align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Sektor</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM tblSektor");
                            echo $stmt->fetchColumn();
                        ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-map-marker-alt fa-2x text-gray-300" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="glass-card-admin border-start border-warning border-4 py-2">
            <div class="row align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">User Menunggu Verifikasi</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status_verifikasi = 'Pending'");
                            echo $stmt->fetchColumn();
                        ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-user-clock fa-2x text-gray-300" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="glass-card-admin">
            <h5 class="fw-bold mb-3">Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h5>
            <p>Ini adalah panel kontrol utama untuk mengelola seluruh data jemaat, kepengurusan, dan jadwal ibadah. Silakan gunakan menu navigasi di sebelah kiri untuk mengelola master data dan fitur lainnya.</p>
        </div>
    </div>
</div>

<?php require_once '../../includes/admin_footer.php'; ?>
