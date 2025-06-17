<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "login");
    exit;
}

$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? false;
unset($_SESSION['errors'], $_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Ubah Password</title>
    <link rel="stylesheet" href="<?= $base_url. "/css/ubah_password.css"?>">
    <link rel="stylesheet" href="<?= $base_url. "/css/sidebar.css"?>">
    <!-- Tambahkan di dalam <head> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <?php include 'sidebar.php'; ?>

    <main class="right-content-password">
        <div class="form-container">
            <h2>Ubah Password</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <p><?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success">Password berhasil diubah!</div>
            <?php endif; ?>

            <form id="formUbahPassword" action="<?= $base_url . "ubah-password-c" ?>"method="POST">
                <div class="form-group">
                    <label for="password_lama">Password Lama</label>
                    <input type="password" name="password_lama" id="password_lama" required>
                </div>

                <div class="form-group">
                    <label for="password_baru">Password Baru</label>
                    <input type="password" name="password_baru" id="password_baru" required>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi_password" id="konfirmasi_password" required>
                </div>

                <button class="btn-ubah-password" type="submit">Ubah Password</button>
            </form>
        </div>
    </main>

    <script>
        window.addEventListener('beforeunload', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });

        window.addEventListener('pageshow', function(event) {
            // Jika halaman dimuat dari cache, pastikan loading disembunyikan
            if (event.persisted) {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        });

        document.getElementById('formUbahPassword').addEventListener('submit', function(e) {
            e.preventDefault(); // cegah submit dulu

            Swal.fire({
                title: 'Konfirmasi Ubah Password',
                text: "Apakah Anda yakin ingin mengubah password?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Ubah',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // lanjut submit
                }
            });
        });
    </script>

</body>

</html>