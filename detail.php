<?php
require 'koneksi.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
$jenis = $_GET['jenis'] ?? null;


if (!$id || !$jenis || !in_array($jenis, ['perorangan', 'perusahaan'])) {
    echo json_encode(['error' => 'ID atau jenis tidak valid']);
    exit;
}

if ($jenis === 'perusahaan') {
    $stmt = $pdo->prepare("SELECT * FROM nasabah_perusahaan WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode(['error' => 'Data tidak ditemukan']);
        exit;
    }

    // Produk lainnya dari tabel produk_lainnya
    // Ambil produk dari dropdown (produk_lainnya)
    $produkList = json_decode($data['produk_lainnya'], true);

    // Ambil produk dari input manual (lainnya)
    $produkManualList = json_decode($data['lainnya'], true);

    $produkMap = [];
    $stmtProduk = $pdo->query("SELECT id, nama_produk FROM produk_lainnya");
    while ($row = $stmtProduk->fetch(PDO::FETCH_ASSOC)) {
        $produkMap[$row['id']] = $row['nama_produk'];
    }

    $data['produk_lainnya_detail'] = [];

    // Tambahkan produk dari dropdown
    if ($produkList) {
        foreach ($produkList as $p) {
            $data['produk_lainnya_detail'][] = [
                'nama' => $produkMap[$p['id']] ?? '[Tidak Dikenal]',
                'jumlah' => $p['jumlah']
            ];
        }
    }

    // Tambahkan produk dari input manual
    if ($produkManualList) {
        foreach ($produkManualList as $p) {
            $data['produk_lainnya_detail'][] = [
                'nama' => $p['nama'],
                'jumlah' => $p['jumlah']
            ];
        }
    }


    // Produk dari input manual (kolom 'lainnya')
    $lainnyaList = json_decode($data['lainnya'] ?? '[]', true);
    $data['lainnya_detail'] = [];
    if (is_array($lainnyaList)) {
        foreach ($lainnyaList as $item) {
            $data['lainnya_detail'][] = [
                'nama' => $item['nama'] ?? '[Tanpa Nama]',
                'jumlah' => $item['jumlah'] ?? 0
            ];
        }
    }

    // Hubungan antar perusahaan
    $hubungan = json_decode($data['nama_perusahaan_hub'], true);
    $data['hubungan'] = is_array($hubungan) ? $hubungan : [];

    echo json_encode($data);
    exit;
} elseif ($jenis === 'perorangan') {
    $stmt = $pdo->prepare("SELECT * FROM nasabah_perorangan WHERE id_perorangan = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode(['error' => 'Data tidak ditemukan']);
        exit;
    }

    // Relasi antar perorangan
    $relasi = json_decode($data['nama_relasi_hub_perorangan'], true);
    $data['relasi'] = is_array($relasi) ? $relasi : [];

    // Hubungan ke perusahaan
    $perusahaanHub = json_decode($data['nama_perusahaan_hub_perorangan'], true);
    $data['hubungan_perusahaan'] = is_array($perusahaanHub) ? $perusahaanHub : [];

    // Produk lainnya dari tabel produk_lainnya
    // Ambil produk dari dropdown (produk_lainnya)
    $produkList = json_decode($data['produk_lainnya'], true);

    // Ambil produk dari input manual (lainnya)
    $produkManualList = json_decode($data['lainnya'], true);

    $produkMap = [];
    $stmtProduk = $pdo->query("SELECT id, nama_produk FROM produk_lainnya");
    while ($row = $stmtProduk->fetch(PDO::FETCH_ASSOC)) {
        $produkMap[$row['id']] = $row['nama_produk'];
    }

    $data['produk_lainnya_detail'] = [];

    // Tambahkan produk dari dropdown
    if ($produkList) {
        foreach ($produkList as $p) {
            $data['produk_lainnya_detail'][] = [
                'nama' => $produkMap[$p['id']] ?? '[Tidak Dikenal]',
                'jumlah' => $p['jumlah']
            ];
        }
    }

    // Tambahkan produk dari input manual
    if ($produkManualList) {
        foreach ($produkManualList as $p) {
            $data['produk_lainnya_detail'][] = [
                'nama' => $p['nama'],
                'jumlah' => $p['jumlah']
            ];
        }
    }


    // Produk dari input manual (kolom 'lainnya')
    $lainnyaList = json_decode($data['lainnya'] ?? '[]', true);
    $data['lainnya_detail'] = [];
    if (is_array($lainnyaList)) {
        foreach ($lainnyaList as $item) {
            $data['lainnya_detail'][] = [
                'nama' => $item['nama'] ?? '[Tanpa Nama]',
                'jumlah' => $item['jumlah'] ?? 0
            ];
        }
    }

    echo json_encode($data);
    exit;
}

echo json_encode(['error' => 'Jenis ID tidak dikenali']);
