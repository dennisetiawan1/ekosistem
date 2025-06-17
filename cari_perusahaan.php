<?php
require 'koneksi.php';

$term = $_GET['term'] ?? '';
$term = trim($term);

if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT nama_perusahaan, cif 
    FROM nasabah_perusahaan 
    WHERE nama_perusahaan LIKE ? 
      AND cif != 'Non-Nasabah' 
    LIMIT 10
");
$stmt->execute(["%$term%"]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format: Nama (CIF)
$formatted = array_map(function ($row) {
    return "{$row['nama_perusahaan']} ({$row['cif']})";
}, $rows);

echo json_encode($formatted);
