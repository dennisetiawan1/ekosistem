<?php
session_start();
require 'koneksi.php';


$stmt = $pdo->query("SELECT id, nama FROM cabang ORDER BY nama");
$cabangList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error_message = '';
if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pegawai BNI</title>
    <link rel="stylesheet" href="<?= $base_url ?>css/login-register.css">
    <base href="<?= $base_url ?>">
</head>

<body>
    <div class="login-container">
        <h2>Login Pegawai BNI</h2>
        <form method="POST" action="<?= $base_url ?>auth" class="login-form">
            <label for="id_cabang">Cabang:</label>
            <select name="id_cabang" id="id_cabang" required>
                <?php foreach ($cabangList as $cabang): ?>
                    <option value="<?= htmlspecialchars($cabang['id']) ?>"><?= htmlspecialchars($cabang['nama']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="npp">NPP Pegawai:</label>
            <input type="text" id="npp" name="npp" placeholder="Masukkan NPP Anda" required>

            <label for="password">Kata Sandi:</label>
            <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
            <div class="register-links">
                <a href="<?= $base_url ?>daftar">Belum Punya Akun ? Daftar</a>
            </div>

            <button type="submit">Login</button>

            <?php if ($error_message): ?>
                <div class="error-message" id="errorMessage">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
    <script>
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.remove();
            }, 2000);
        }
        
        // Set base URL for JavaScript
        const baseUrl = '<?= $base_url ?>';
    </script>
</body>

</html>