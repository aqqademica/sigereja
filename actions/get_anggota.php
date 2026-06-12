<?php
require_once '../includes/session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Cek autentikasi
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$id_keluarga = $_GET['id_keluarga'] ?? '';
if ($id_keluarga) {
    // Ambil jemaat yang aktif (bukan yang sudah meninggal/mutasi)
    $stmt = $pdo->prepare("SELECT id_jemaat, nama_lengkap, status_dalam_keluarga FROM tblJemaat WHERE id_keluarga = ? AND status_keanggotaan = 'Aktif' ORDER BY tanggal_lahir ASC");
    $stmt->execute([$id_keluarga]);
    $anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($anggota);
} else {
    echo json_encode([]);
}
