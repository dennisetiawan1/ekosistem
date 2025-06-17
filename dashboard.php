<?php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location:". $base_url. "login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Nasabah</title>
    <link rel="stylesheet" href="<?= $base_url. "/css/dashboard.css"?>">
    <link rel="stylesheet" href="<?= $base_url. "/css/informasi.css"?>">
    <base href="<?= $base_url ?>">
</head>

<body>

    <?php include 'sidebar.php' ?>

    <main class="right-content-dashboard">
        <div class="header">
            <img src="img/welcome.svg" alt="" width="220">
            <h2>Selamat Datang, <strong><?= htmlspecialchars($namaPegawai) ?></strong></h2>
        </div>
        <div class="btn-group">
            <a href="<?= $base_url. "form/perorangan" ?>" class="menu-icon">
                <img src="img/perorangan.svg" alt="Form Perorangan" width="180" />
                <h3>Perorangan</h3>
                <p>• Tambah nasabah</p>
                <p>• Tambah non nasabah</p>
            </a>
            <a href="<?= $base_url. "form/perusahaan" ?>" class="menu-icon">
                <img src="img/perusahaan.svg" alt="Form Non Perorangan" width="180" />
                <h3>Non Perorangan</h3>
                <p>• Tambah nasabah</p>
                <p>• Tambah non nasabah</p>
            </a>
        </div>
        <!-- <div style="@media (max-width){display:hidden}">
            f
        </div> -->
    </main>

    <div class="modal"></div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script>
        const menuButtons = document.querySelectorAll('.menu-icon');

        menuButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('loadingOverlay').style.display = 'flex';
                const url = this.getAttribute('href');
                setTimeout(() => {
                    window.location.href = url;
                }, 500);
            });
        });

        window.addEventListener('beforeunload', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });

        window.addEventListener('pageshow', function(event) {
            // Jika halaman dimuat dari cache, pastikan loading disembunyikan
            if (event.persisted) {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        });
    </script>

</body>

</html>