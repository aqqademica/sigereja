<?php
require_once 'config/database.php';

// Fetch warta for the current week (limit to recent 10 to avoid too long slides)
$wartas = $pdo->query("SELECT * FROM tblWartaJemaat WHERE status_publish = 'Published' ORDER BY tanggal_terbit DESC, id_warta DESC LIMIT 10")->fetchAll();

if (empty($wartas)) {
    $wartas = [['judul' => 'Selamat Beribadah', 'kategori' => 'Umum', 'isi_warta' => 'Tuhan Memberkati Pelayanan Kita Bersama.']];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warta Jemaat - Slide View</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            background-color: #0f172a; /* Dark background */
            color: white;
            font-family: 'Inter', sans-serif;
            overflow: hidden; /* Hide scrollbars for presentation */
        }
        
        .slide-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem;
            text-align: center;
        }

        .slide-item {
            display: none;
            width: 100%;
            max-width: 1000px;
            animation: fadeZoom 1s ease-in-out forwards;
        }

        .slide-item.active {
            display: block;
        }

        @keyframes fadeZoom {
            0% { opacity: 0; transform: scale(0.95); }
            100% { opacity: 1; transform: scale(1); }
        }

        .warta-category {
            font-size: 1.5rem;
            color: #fbbf24; /* Warning/Yellow */
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .warta-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 2rem;
            line-height: 1.2;
            text-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }

        .warta-content {
            font-size: 2rem;
            line-height: 1.6;
            color: #cbd5e1; /* Light Slate */
        }

        /* Running Text (Marquee) at the bottom */
        .running-text-container {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: #1e293b;
            color: #fff;
            padding: 15px 0;
            font-size: 1.5rem;
            font-weight: 500;
            border-top: 3px solid #3b82f6;
            z-index: 1000;
        }
        
        .controls {
            position: fixed;
            bottom: 80px;
            right: 20px;
            opacity: 0.2;
            transition: opacity 0.3s;
            z-index: 1001;
        }
        .controls:hover { opacity: 1; }
    </style>
</head>
<body>

<div class="slide-container" id="slider">
    <?php foreach($wartas as $idx => $w): ?>
    <div class="slide-item <?= $idx === 0 ? 'active' : '' ?>">
        <div class="warta-category"><?= htmlspecialchars($w['kategori']) ?></div>
        <div class="warta-title"><?= htmlspecialchars($w['judul']) ?></div>
        <div class="warta-content">
            <?= nl2br(htmlspecialchars(strlen($w['isi_warta']) > 400 ? substr($w['isi_warta'], 0, 400) . '...' : $w['isi_warta'])) ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="controls">
    <button class="btn btn-outline-light btn-lg rounded-circle me-2" onclick="prevSlide()">&#10094;</button>
    <button id="playBtn" class="btn btn-light btn-lg rounded-circle me-2" onclick="togglePlay()">&#10074;&#10074;</button>
    <button class="btn btn-outline-light btn-lg rounded-circle" onclick="nextSlide()">&#10095;</button>
</div>

<div class="running-text-container">
    <marquee behavior="scroll" direction="left" scrollamount="10">
        Selamat Datang di Ibadah Hari Ini | Gereja Membawa Damai Sejahtera | Harap Menonaktifkan Telepon Seluler Anda Selama Ibadah Berlangsung | Tuhan Yesus Memberkati.
    </marquee>
</div>

<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide-item');
    const totalSlides = slides.length;
    let slideInterval;
    let isPlaying = true;
    const intervalTime = 10000; // 10 detik per slide

    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        slides[index].classList.add('active');
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        showSlide(currentSlide);
    }

    function togglePlay() {
        if (isPlaying) {
            clearInterval(slideInterval);
            document.getElementById('playBtn').innerHTML = '&#9658;'; // Play icon
            isPlaying = false;
        } else {
            nextSlide();
            slideInterval = setInterval(nextSlide, intervalTime);
            document.getElementById('playBtn').innerHTML = '&#10074;&#10074;'; // Pause icon
            isPlaying = true;
        }
    }

    // Start auto slide
    slideInterval = setInterval(nextSlide, intervalTime);

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowRight') nextSlide();
        if (e.key === 'ArrowLeft') prevSlide();
        if (e.key === ' ') togglePlay(); // Spacebar toggles play/pause
    });
</script>

</body>
</html>
