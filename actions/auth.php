<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

// H5: Verify CSRF token on every POST (except logout which destroys session anyway)
$action = $_POST['action'] ?? '';
if ($action !== 'logout') {
    csrf_verify('../login.php');
}


// Handle old 'register' action if still somehow called
if ($action === 'register') {
    $_SESSION['error_msg'] = "Gunakan form pendaftaran terbaru.";
    header("Location: ../register.php");
    exit;
}

if ($action === 'register_linked') {
    // 1. Sudah Terdaftar
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($username) || empty($email) || empty($no_hp) || empty($nama_lengkap) || empty($tanggal_lahir) || empty($password)) {
        $_SESSION['error_msg'] = "Semua field harus diisi.";
        header("Location: ../register.php");
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error_msg'] = "Password minimal 8 karakter.";
        header("Location: ../register.php");
        exit;
    }

    if ($password !== $password_confirm) {
        $_SESSION['error_msg'] = "Password tidak cocok.";
        header("Location: ../register.php");
        exit;
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $_SESSION['error_msg'] = "Username atau email sudah terdaftar di sistem kami.";
        header("Location: ../register.php");
        exit;
    }

    // Lookup tblJemaat
    $stmtJemaat = $pdo->prepare("SELECT id_jemaat, user_id FROM tblJemaat WHERE no_hp = ? AND alamat_email_aktif = ? AND nama_lengkap = ? AND tanggal_lahir = ?");
    $stmtJemaat->execute([$no_hp, $email, $nama_lengkap, $tanggal_lahir]);
    $jemaatData = $stmtJemaat->fetch();

    if ($jemaatData) {
        if (!empty($jemaatData['user_id'])) {
            $_SESSION['error_msg'] = "Data jemaat tersebut sudah ditautkan ke akun lain.";
            header("Location: ../register.php");
            exit;
        }

        // Create user
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmtUser = $pdo->prepare("INSERT INTO users (username, email, password, role, status_verifikasi) VALUES (?, ?, ?, 'Jemaat', 'Approved Majelis')");
        if ($stmtUser->execute([$username, $email, $hashed_password])) {
            $new_user_id = $pdo->lastInsertId();
            
            // Link to tblJemaat
            $stmtUpdate = $pdo->prepare("UPDATE tblJemaat SET user_id = ? WHERE id_jemaat = ?");
            $stmtUpdate->execute([$new_user_id, $jemaatData['id_jemaat']]);

            $_SESSION['success_msg'] = "Akun berhasil dibuat dan ditautkan ke data Jemaat. Silakan login.";
            header("Location: ../login.php");
            exit;
        } else {
            $_SESSION['error_msg'] = "Terjadi kesalahan sistem saat membuat akun.";
            header("Location: ../register.php");
            exit;
        }

    } else {
        $_SESSION['error_msg'] = "Data tidak ditemukan di database gereja. Pastikan No. HP, Email, Nama Lengkap, dan Tanggal Lahir sama persis dengan yang didaftarkan Pengurus.";
        header("Location: ../register.php");
        exit;
    }

} elseif ($action === 'register_new_jemaat') {
    // 2. Belum Terdaftar -> Isi Data Lengkap (Pending)
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $id_sektor = !empty($_POST['id_sektor']) ? (int)$_POST['id_sektor'] : null;
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $status_pernikahan = $_POST['status_pernikahan'] ?? '';
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($nama_lengkap) || empty($id_sektor) || empty($jenis_kelamin) || empty($tanggal_lahir)) {
        $_SESSION['error_msg'] = "Harap lengkapi semua field yang wajib diisi.";
        header("Location: ../register.php");
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error_msg'] = "Password minimal 8 karakter.";
        header("Location: ../register.php");
        exit;
    }

    if ($password !== $password_confirm) {
        $_SESSION['error_msg'] = "Password tidak cocok.";
        header("Location: ../register.php");
        exit;
    }

    // Check user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $_SESSION['error_msg'] = "Username atau email sudah digunakan.";
        header("Location: ../register.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Create User (Status Pending)
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmtUser = $pdo->prepare("INSERT INTO users (username, email, password, role, status_verifikasi) VALUES (?, ?, ?, 'Jemaat', 'Pending')");
        $stmtUser->execute([$username, $email, $hashed_password]);
        $new_user_id = $pdo->lastInsertId();

        // 2. Insert into tblJemaat
        $stmtJemaat = $pdo->prepare("INSERT INTO tblJemaat (user_id, nama_lengkap, jenis_kelamin, tanggal_lahir, status_pernikahan, id_sektor, alamat, no_hp, alamat_email_aktif, status_keanggotaan) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')");
        $stmtJemaat->execute([
            $new_user_id, $nama_lengkap, $jenis_kelamin, $tanggal_lahir, 
            $status_pernikahan, $id_sektor, $alamat, $no_hp, $email
        ]);

        $pdo->commit();
        $_SESSION['success_msg'] = "Pendaftaran berhasil! Akun Anda sedang menunggu verifikasi oleh Pengurus Sektor.";
        header("Location: ../login.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Terjadi kesalahan sistem: " . $e->getMessage();
        header("Location: ../register.php");
        exit;
    }

} elseif ($action === 'register_guest') {
    // 3. Belum Terdaftar -> Daftar Akun Tamu
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error_msg'] = "Semua field harus diisi.";
        header("Location: ../register.php");
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error_msg'] = "Password minimal 8 karakter.";
        header("Location: ../register.php");
        exit;
    }

    if ($password !== $password_confirm) {
        $_SESSION['error_msg'] = "Password tidak cocok.";
        header("Location: ../register.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $_SESSION['error_msg'] = "Username atau email sudah terdaftar.";
        header("Location: ../register.php");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    // Guest accounts are approved immediately to view jadwal ibadah
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status_verifikasi) VALUES (?, ?, ?, 'Jemaat', 'Approved Majelis')");
    if ($stmt->execute([$username, $email, $hashed_password])) {
        $_SESSION['success_msg'] = "Pendaftaran Akun Tamu berhasil! Silakan login untuk melihat Jadwal Ibadah.";
        header("Location: ../login.php");
    } else {
        $_SESSION['error_msg'] = "Terjadi kesalahan saat pendaftaran.";
        header("Location: ../register.php");
    }
    exit;

} elseif ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status_verifikasi'] === 'Pending') {
            $_SESSION['error_msg'] = "Akun Anda masih berstatus <strong>Pending</strong> dan belum diverifikasi oleh Pengurus Sektor. Harap menunggu atau hubungi Ketua Sektor Anda.";
            header("Location: ../login.php");
            exit;
        }
        if ($user['status_verifikasi'] === 'Verified Sektor') {
            $_SESSION['error_msg'] = "Akun Anda telah diverifikasi oleh Ketua Sektor dan sedang menunggu persetujuan akhir dari <strong>Majelis Gereja</strong>. Harap bersabar.";
            header("Location: ../login.php");
            exit;
        }

        regenerate_session(); // Prevent session fixation

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] === 'Jemaat') {
            $stmt_jemaat = $pdo->prepare("SELECT id_jemaat FROM tblJemaat WHERE user_id = ?");
            $stmt_jemaat->execute([$user['id']]);
            $jemaat = $stmt_jemaat->fetch();
            if ($jemaat) {
                $_SESSION['id_jemaat'] = $jemaat['id_jemaat'];
            }
        }

        header("Location: ../index.php");
    } else {
        $_SESSION['error_msg'] = "Username/Email atau Password salah.";
        header("Location: ../login.php");
    }
    exit;

} elseif ($action === 'logout') {
    session_destroy();
    header("Location: ../index.php");
    exit;
}
