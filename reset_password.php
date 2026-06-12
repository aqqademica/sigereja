<?php
require_once 'includes/header.php';
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$token = $_GET['token'] ?? '';
if (empty($token)) {
    $_SESSION['error_msg'] = "Token tidak valid.";
    header("Location: forgot_password.php");
    exit;
}

// Cek token valid
$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->execute([$token]);
if (!$stmt->fetch()) {
    $_SESSION['error_msg'] = "Token tidak valid atau sudah kedaluwarsa.";
    header("Location: forgot_password.php");
    exit;
}
?>

<div class="container mt-5 mb-5 fade-in">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card">
                <div class="text-center mb-4">
                    <i class="fas fa-key fa-3x text-success mb-3"></i>
                    <h2 class="fw-bold">Ganti Password</h2>
                    <p class="text-muted">Silakan masukkan password baru Anda.</p>
                </div>

                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                    </div>
                <?php endif; ?>

                <form action="actions/reset_password.php" method="POST">
    <?= csrf_field() ?>
<input type="hidden" name="action" value="do_reset">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" class="form-control form-control-custom border-start-0" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirm" class="form-label fw-medium">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" class="form-control form-control-custom border-start-0" id="password_confirm" name="password_confirm" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 mb-3" style="border-radius: 8px;">
                        <i class="fas fa-save me-2"></i>Simpan Password Baru
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
