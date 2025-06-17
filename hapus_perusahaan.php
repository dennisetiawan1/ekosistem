<?php
require 'koneksi.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metode tidak diizinkan']);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    echo json_encode(['error' => 'ID tidak ditemukan']);
    exit;
}

// Lakukan penghapusan dengan foreign key constraints (hapus produk dan hubungan terlebih dahulu jika diperlukan)
try {
    // Optional: hapus dari tabel terkait terlebih dahulu jika tidak menggunakan ON DELETE CASCADE
    // Hapus nasabah utama
    $stmt = $pdo->prepare("DELETE FROM nasabah_perusahaan WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
