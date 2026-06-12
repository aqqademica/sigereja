<?php
require_once '../includes/session.php';
require_once '../config/database.php';

// Cleanup expired tokens
$pdo->query("UPDATE users SET reset_token = NULL, reset_token_expiry = NULL WHERE reset_token_expiry < NOW()");

$action = $_POST['action'] ?? '';

if ($action === 'request_reset') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $_SESSION['error_msg'] = "Email wajib diisi.";
        header("Location: ../forgot_password.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate Token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $update->execute([$token, $expiry, $user['id']]);

        // SIMULASI PENGIRIMAN EMAIL (karena di local dev biasanya mail() tidak jalan)
        // Di environment nyata, gunakan PHPMailer atau mail()
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
        
        // Log the link for developer testing
        error_log("Reset link for $email: $reset_link");

        $_SESSION['success_msg'] = "Tautan reset password telah dikirim ke email Anda. (Cek error_log di local dev: $reset_link)";
    } else {
        // Jangan beri tahu secara eksplisit jika email tidak ditemukan (security best practice),
        // tapi untuk memudahkan user/dev, kita tampilkan error.
        $_SESSION['error_msg'] = "Email tidak ditemukan di sistem kami.";
    }

    header("Location: ../forgot_password.php");
    exit;
} elseif ($action === 'do_reset') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($token) || empty($password) || empty($password_confirm)) {
        $_SESSION['error_msg'] = "Semua field harus diisi.";
        header("Location: ../reset_password.php?token=$token");
        exit;
    }

    if ($password !== $password_confirm) {
        $_SESSION['error_msg'] = "Password tidak cocok.";
        header("Location: ../reset_password.php?token=$token");
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $update->execute([$hashed_password, $user['id']]);

        $_SESSION['success_msg'] = "Password berhasil diubah. Silakan login dengan password baru.";
        header("Location: ../login.php");
    } else {
        $_SESSION['error_msg'] = "Token tidak valid atau sudah kedaluwarsa.";
        header("Location: ../forgot_password.php");
    }
    exit;
} else {
    header("Location: ../index.php");
    exit;
}
