<?php
require 'koneksi.php';
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sidebar</title>
  <link rel="stylesheet" href="/BNI%20v12/css/sidebar.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <base href="<?= $base_url ?>">
</head>
<button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
<aside class="sidebar">
  <div>
    <h2>Dashboard</h2>
    <a href="<?= $base_url . "dashboard" ?>" class="dashboard-menu <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
      <img src="img/dashboard-icon.svg" alt="">
      <p>Dashboard</p>
    </a>
    <a href="<?= $base_url . "informasi/perusahaan" ?>" class="dashboard-menu <?= basename($_SERVER['PHP_SELF']) == 'informasi_perusahaan.php' ? 'active' : '' ?>">
      <img src="img/perusahaan.svg" alt="" width="24">
      <p>Nasabah Perusahaan</p>
    </a>
    <a href="<?= $base_url . "informasi/perorangan" ?>" class="dashboard-menu <?= basename($_SERVER['PHP_SELF']) == '"informasi-perorangan.php' ? 'active' : '' ?>">
      <img src="img/perorangan.svg" alt="" width="24">
      <p>Nasabah Perorangan</p>
    </a>
    <a href="<?= $base_url . "ubah-password" ?>" class="dashboard-menu <?= basename($_SERVER['PHP_SELF']) == 'ubah_password.php' ? 'active' : '' ?>">
      <img src="img/key.svg" alt="" width="24">
      <p>Ubah Password</p>
    </a>
  </div>
  <button class="logout" onclick="confirmLogout()">Keluar</button>

</aside>

<div class="loading-overlay" id="loadingOverlay">
  <div class="spinner"></div>
</div>

<script>
  function confirmLogout() {
    Swal.fire({
      title: 'Yakin ingin keluar?',
      text: "Sesi Anda akan diakhiri.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e74c3c',
      cancelButtonColor: '#aaa',
      confirmButtonText: 'Ya, Keluar',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {

        document.getElementById('loadingOverlay').style.display = 'flex';
        setTimeout(() => {
          window.location.href = '/BNI%20v12/logout';
        }, 500);
      }
    });
  }

  const links = document.querySelectorAll('.dashboard-menu');

  links.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      document.getElementById('loadingOverlay').style.display = 'flex';
      const url = this.getAttribute('href');
      setTimeout(() => {
        window.location.href = url;
      }, 500); // Delay sedikit agar loader terlihat
    });
  });
    function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
  }
</script>