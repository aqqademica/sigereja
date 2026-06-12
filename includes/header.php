<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../actions/notifikasi.php';
require_once __DIR__ . '/csrf.php'; // H5: CSRF protection

$notif_count = 0;
$notif_list  = [];
if (isset($_SESSION['user_id'])) {
    $notif_count = hitung_notifikasi($pdo, $_SESSION['user_id']);
    $notif_list  = ambil_notifikasi($pdo, $_SESSION['user_id'], 8);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI Gereja</title>
    <meta name="description" content="Sistem Informasi Gereja modern untuk kemudahan pelayanan, pendaftaran, dan informasi jemaat.">
    <meta name="theme-color" content="#1e1b4b">
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/icon-192.png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom PWA CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<main class="flex-shrink-0">

<!-- Register SW -->
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js').catch(() => {});
}
</script>
