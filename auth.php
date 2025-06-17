<?php
session_start();
require 'koneksi.php';


// Store scroll position if provided
if (isset($_POST['npp']) && isset($_POST['password'])) {
    $_SESSION['scrollY'] = $_POST['scrollY'] ?? 0;
}

// Validate required fields
if (empty($_POST['id_cabang']) || empty($_POST['npp']) || empty($_POST['password'])) {
    $_SESSION['login_error'] = 'Semua field harus diisi';
    header("Location: " . $base_url . "login");
    exit;
}

$id_cabang = $_POST['id_cabang'];
$npp = $_POST['npp'];
$password = $_POST['password'];

// Database query
$stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id_cabang = ? AND npp = ?");
$stmt->execute([$id_cabang, $npp]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password_hash'])) {
    // Successful login
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['npp'] = $user['npp'];
    $_SESSION['id_cabang'] = $user['id_cabang'];
    $_SESSION['nama_pegawai'] = $user['nama_pegawai']; // Store user's name in session
    
    header("Location: " . $base_url . "dashboard");
    exit;
} else {
    // Failed login
    $error = $user ? 'Password salah' : 'NPP tidak ditemukan';
    $_SESSION['login_error'] = $error;
    $_SESSION['old_input'] = [
        'id_cabang' => $id_cabang,
        'npp' => $npp
    ];
    
    header("Location: " . $base_url . "login");
    exit;
}