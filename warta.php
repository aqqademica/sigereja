<?php
require_once 'includes/header.php';

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // 5 warta per halaman untuk mobile
$offset = ($page - 1) * $limit;

// Fetch active warta
$stmt = $pdo->prepare("SELECT * FROM tblWartaJemaat WHERE status_publish = 'Published' ORDER BY tanggal_terbit DESC, id_warta DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$wartas = $stmt->fetchAll();

// Total pages
$total = $pdo->query("SELECT COUNT(*) FROM tblWartaJemaat WHERE status_publish = 'Published'")->fetchColumn();
$totalPages = ceil($total / $limit);
?>

<div class="container mt-5 mb-5 fade-in" style="max-width: 800px;">
    <div class="text-center mb-5">
        <i class="fas fa-newspaper fa-3x text-primary mb-3"></i>
        <h2 class="fw-bold">Warta Jemaat</h2>
        <p class="text-muted">Informasi dan pengumuman terbaru seputar pelayanan gereja.</p>
        <a href="warta_slide.php" target="_blank" class="btn btn-sm btn-outline-secondary mt-2"><i class="fas fa-desktop"></i> Mode Presentasi (Slide)</a>
    </div>

    <?php if(empty($wartas)): ?>
        <div class="text-center py-5 glass-card">
            <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
            <p class="text-muted mb-0">Belum ada warta jemaat yang diterbitkan saat ini.</p>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach($wartas as $w): ?>
        <div class="col-12 mb-4">
            <div class="glass-card h-100 position-relative" style="padding: 2rem;">
                <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                    <div>
                        <h4 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($w['judul']) ?></h4>
                        <span class="badge bg-primary-custom text-white"><i class="fas fa-tag me-1"></i> <?= htmlspecialchars($w['kategori']) ?></span>
                    </div>
                    <div class="text-end">
                        <span class="d-block fw-bold text-primary" style="font-size: 1.2rem;"><?= date('d', strtotime($w['tanggal_terbit'])) ?></span>
                        <span class="text-muted small text-uppercase"><?= date('M Y', strtotime($w['tanggal_terbit'])) ?></span>
                    </div>
                </div>
                
                <div class="warta-content text-secondary" style="line-height: 1.8;">
                    <?= nl2br(htmlspecialchars($w['isi_warta'])) ?>
                </div>
                
                <!-- Decorative element -->
                <div class="position-absolute bottom-0 end-0 p-3 opacity-25">
                    <i class="fas fa-church fa-3x text-light"></i>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
            </li>
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<style>
/* Custom Pagination Styling for aesthetics */
.pagination .page-link {
    border-radius: 8px;
    margin: 0 4px;
    border: none;
    color: #4f46e5;
    background: rgba(255,255,255,0.8);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.pagination .page-item.active .page-link {
    background: #4f46e5;
    color: white;
}
</style>

<?php require_once 'includes/footer.php'; ?>
