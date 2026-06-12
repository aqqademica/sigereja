<?php
/**
 * Notifikasi Helper Functions
 * Call these throughout the app to create notification records.
 */

/**
 * Create a notification for a specific user.
 */
function kirim_notifikasi(PDO $pdo, int $user_id, string $judul, string $pesan, string $url = ''): void {
    try {
        $pdo->prepare("INSERT INTO tblNotifikasi (user_id, judul, pesan, url) VALUES (?, ?, ?, ?)")
            ->execute([$user_id, $judul, $pesan, $url]);
    } catch (Exception $e) {
        // Silently fail — notifications must not break core workflows
    }
}

/**
 * Mark a notification as read.
 */
function tandai_dibaca(PDO $pdo, int $notif_id, int $user_id): void {
    $pdo->prepare("UPDATE tblNotifikasi SET is_read = 1 WHERE id = ? AND user_id = ?")
        ->execute([$notif_id, $user_id]);
}

/**
 * Mark all notifications as read for a user.
 */
function tandai_semua_dibaca(PDO $pdo, int $user_id): void {
    $pdo->prepare("UPDATE tblNotifikasi SET is_read = 1 WHERE user_id = ?")
        ->execute([$user_id]);
}

/**
 * Get unread notification count for a user.
 */
function hitung_notifikasi(PDO $pdo, int $user_id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tblNotifikasi WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int) $stmt->fetchColumn();
}

/**
 * Get recent notifications for a user (for dropdown).
 */
function ambil_notifikasi(PDO $pdo, int $user_id, int $limit = 10): array {
    $limit = (int)$limit;
    $stmt = $pdo->prepare("SELECT * FROM tblNotifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}
