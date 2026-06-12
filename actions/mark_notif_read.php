<?php
require_once '../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../actions/notifikasi.php';
if (isset($_SESSION['user_id'])) {
    tandai_semua_dibaca($pdo, $_SESSION['user_id']);
}
header('Content-Type: application/json');
echo json_encode(['ok' => true]);
