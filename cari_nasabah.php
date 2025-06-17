<?php
require 'koneksi.php';

$term = $_GET['term'] ?? '';
$term = trim($term);

if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

// Query hanya nasabah dengan CIF valid (bukan Non-Nasabah)
$stmt = $pdo->prepare("
    SELECT nama_perorangan, cif_perorangan 
    FROM nasabah_perorangan 
    WHERE nama_perorangan LIKE ? 
      AND cif_perorangan != 'Non-Nasabah' 
    LIMIT 10
");
$stmt->execute(["%$term%"]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format: Nama (CIF)
$formatted = array_map(function ($row) {
    return "{$row['nama_perorangan']} ({$row['cif_perorangan']})";
}, $rows);

echo json_encode($formatted);
