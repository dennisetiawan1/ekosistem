<?php
session_start();
require 'koneksi.php';


$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? false;

unset($_SESSION['errors'], $_SESSION['success']);

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $id_cabang = $_POST['id_cabang'];
    $npp = trim($_POST['npp']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    $errors = [];

    if ($password !== $confirm) {
        $errors[] = "Password dan konfirmasi tidak cocok.";
    }

    // Cek apakah NPP sudah terdaftar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pegawai WHERE npp = ?");
    $stmt->execute([$npp]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "NPP sudah terdaftar.";
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO pegawai (id_cabang, npp, nama_pegawai, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_cabang, $npp, $nama, $password_hash]);
        $_SESSION['success'] = true;
    } else {
        $_SESSION['errors'] = $errors;
    }

    header("Location:"  . $base_url . "daftar");
    exit;
}

// Ambil daftar cabang
$stmt = $pdo->query("SELECT id, nama FROM cabang ORDER BY nama");
$cabangList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/BNI%20v12/css/login-register.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <base href="<?= $base_url ?>">
</head>

<body>
    <div class="register-container">
        <h2>Registrasi Pegawai</h2>

        <form method="POST" class="register-form">
            <label for="id_cabang">Cabang:</label>
            <select name="id_cabang" id="id_cabang" required>
                <option value="">-- Pilih Cabang --</option>
                <?php foreach ($cabangList as $cabang): ?>
                    <option value="<?= htmlspecialchars($cabang['id']) ?>"><?= htmlspecialchars($cabang['nama']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="nama">Nama:</label>
            <input type="text" name="nama" id="nama" required>

            <label for="npp">NPP Pegawai:</label>
            <input type="text" name="npp" id="npp" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <label for="confirm_password">Konfirmasi Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <button type="submit">Daftar</button>
        </form>

        <div class="login-link">
            <a href="<?= $base_url . "login" ?>">← Kembali ke Login</a>
        </div>
    </div>
    <?php if ($success): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Registrasi berhasil. Silakan login.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#005e6a'
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Registrasi Gagal',
                html: `<ul style="text-align: left;">
            <?= implode('', array_map(fn($e) => "<li>" . htmlspecialchars($e) . "</li>", $errors)) ?>
        </ul>`,
                confirmButtonText: 'Tutup'
            });
        </script>
    <?php endif; ?>

</body>

</html>