<?php
$host = 'localhost';
$db   = 'bni_nasabah_ekosistem';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];



try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('DB Error: ' . $e->getMessage());
}

$ids = $_SESSION['user_id'] ?? null;

$namaPegawai = 'Pegawai';
if ($ids) {
    $stmt = $pdo->prepare("SELECT nama_pegawai FROM pegawai WHERE id = ?");
    $stmt->execute([$ids]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $namaPegawai = $row['nama_pegawai'];
    }
}

// Get dynamic base URL
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/BNI%20v12/";

?>
