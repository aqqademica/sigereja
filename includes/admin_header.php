<?php
ob_start();
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../actions/notifikasi.php';
require_once __DIR__ . '/csrf.php'; // H5: CSRF protection

// Cek akses admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Super Admin', 'Admin Sistem', 'Sekretaris', 'Ketua Sektor', 'Pendeta'])) {
    header("Location: ../../login.php");
    exit;
}

$notif_count = hitung_notifikasi($pdo, $_SESSION['user_id']);
$notif_list  = ambil_notifikasi($pdo, $_SESSION['user_id'], 8);

// Handle mark-all-read via AJAX
if (isset($_GET['mark_notif_read'])) {
    tandai_semua_dibaca($pdo, $_SESSION['user_id']);
    echo json_encode(['ok' => true]); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - SI Gereja</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
<div class="wrapper">
    <!-- ========== SIDEBAR ========== -->
    <?php include __DIR__ . '/admin_sidebar.php'; ?>
    <!-- ========== MAIN ========== -->
    <div class="main-content">
        <!-- ========== TOPBAR ========== -->
        <?php include __DIR__ . '/admin_topbar.php'; ?>
        <!-- ========== CONTENT ========== -->
        <div class="content-area fade-in">
