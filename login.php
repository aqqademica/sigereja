<?php
require_once 'includes/session.php';
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'includes/header.php';
?>

<div class="container mt-5 fade-in">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card">
                <div class="text-center mb-4">
                    <i class="fas fa-church fa-3x text-primary mb-3"></i>
                    <h2 class="fw-bold">Shalom!</h2>
                    <p class="text-muted">Silakan masuk ke akun Anda</p>
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

                <form action="actions/auth.php" method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="login">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-medium">Username / Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" class="form-control form-control-custom border-start-0" id="username" name="username" required placeholder="Masukkan username atau email">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label fw-medium">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" class="form-control form-control-custom border-start-0" id="password" name="password" required placeholder="Masukkan password">
                        </div>
                        <div class="text-end mt-1">
                            <a href="forgot_password.php" class="text-decoration-none small text-primary">Lupa Password?</a>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </button>
                    <div class="text-center">
                        <span class="text-muted">Belum punya akun?</span> <a href="register.php" class="text-decoration-none fw-bold text-primary">Daftar sekarang</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
