<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header( "Location:" . $base_url . "login");
    exit;
}

$user_id = $_SESSION['user_id'];
$password_lama = $_POST['password_lama'];
$password_baru = $_POST['password_baru'];
$konfirmasi = $_POST['konfirmasi_password'];

$errors = [];

// Ambil hash password dari database
$stmt = $pdo->prepare("SELECT password_hash FROM pegawai WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || !password_verify($password_lama, $user['password_hash'])) {
    $errors[] = "Password lama salah.";
}

if ($password_baru !== $konfirmasi) {
    $errors[] = "Konfirmasi password tidak cocok.";
}

if (empty($errors)) {
    $password_baru_hash = password_hash($password_baru, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE pegawai SET password_hash = ? WHERE id = ?");
    $stmt->execute([$password_baru_hash, $user_id]);
    $_SESSION['success'] = true;
} else {
    $_SESSION['errors'] = $errors;
}

header("Location:" . $base_url . "ubah-password");
exit;
