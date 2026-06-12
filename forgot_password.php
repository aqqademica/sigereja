<?php
require_once 'includes/header.php';
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>

<div class="container mt-5 mb-5 fade-in">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card">
                <div class="text-center mb-4">
                    <i class="fas fa-envelope-open-text fa-3x text-warning mb-3"></i>
                    <h2 class="fw-bold">Lupa Password</h2>
                    <p class="text-muted">Masukkan email Anda untuk menerima tautan reset password.</p>
                </div>

                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                    </div>
                <?php endif; ?>

                <form action="actions/reset_password.php" method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="request_reset">
                    <div class="mb-4">
                        <label for="email" class="form-label fw-medium">Email Terdaftar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-at text-muted"></i></span>
                            <input type="email" class="form-control form-control-custom border-start-0" id="email" name="email" required placeholder="nama@email.com">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 mb-3 text-dark fw-bold" style="border-radius: 8px;">
                        <i class="fas fa-paper-plane me-2"></i>Kirim Tautan Reset
                    </button>
                    <div class="text-center">
                        <a href="login.php" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-1"></i> Kembali ke Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
