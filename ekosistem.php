<?php
require 'koneksi.php';
header('Content-Type: application/json');

$idAwal = $_GET['id'] ?? null;
$jenisAwal = $_GET['jenis'] ?? null;

if (!$idAwal || !$jenisAwal) {
    echo json_encode(['error' => 'ID atau jenis tidak ditemukan']);
    exit;
}

$idMap = [];
$namaMap = [];

// Ambil data perusahaan
$stmt = $pdo->query("SELECT id, nama_perusahaan, cif, nama_perusahaan_hub FROM nasabah_perusahaan");
$perusahaanList = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($perusahaanList as $p) {
    $id = 'perusahaan_' . $p['id'];
    $idMap[$id] = [
        'jenis' => 'perusahaan',
        'id' => $p['id'],
        'nama_perusahaan' => $p['nama_perusahaan'],
        'cif' => $p['cif'],
        'nama_perusahaan_hub' => $p['nama_perusahaan_hub']
    ];
    $namaMap[trim(strtolower($p['nama_perusahaan']))] = $id;
}

// Ambil data perorangan
$stmt = $pdo->query("SELECT id_perorangan, nama_perorangan, cif_perorangan, nama_perusahaan_hub_perorangan, nama_relasi_hub_perorangan FROM nasabah_perorangan");
$peroranganList = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($peroranganList as $p) {
    $id = 'perorangan_' . $p['id_perorangan'];
    $idMap[$id] = [
        'jenis' => 'perorangan',
        'id' => $p['id_perorangan'],
        'nama' => $p['nama_perorangan'],
        'cif' => $p['cif_perorangan'],
        'nama_perusahaan_hub_perorangan' => $p['nama_perusahaan_hub_perorangan'],
        'nama_relasi_hub_perorangan' => $p['nama_relasi_hub_perorangan']
    ];
    $namaMap[trim(strtolower($p['nama_perorangan']))] = $id;
}

$fullId = $jenisAwal . '_' . $idAwal;
if (!isset($idMap[$fullId])) {
    echo json_encode(['error' => 'ID tidak ditemukan']);
    exit;
}

$nodes = [];
$edges = [];
$visited = [];

function telusuri($id, &$nodes, &$edges, &$visited, $idMap, $namaMap, $pdo) {
    if (isset($visited[$id]) || !isset($idMap[$id])) return;
    $visited[$id] = true;

    $data = $idMap[$id];
    $jenis = $data['jenis'];
    $label = $jenis === 'perorangan' ? $data['nama'] : $data['nama_perusahaan'];
    $cif = $data['cif'];

// Tentukan warna node berdasarkan kondisi
if ($id === ($_GET['jenis'] . '_' . $_GET['id'])) {
    $color = '#7a95ff'; // Node awal (ekosistem yang diklik)
} else {
    if ($jenis === 'perorangan') {
        if (strtolower(trim($data['cif'])) === 'non-nasabah') {
            $color = '#f29b7c'; // Perorangan bukan nasabah
        } elseif (empty($data['cif'])) {
            $color = '#fffeb0'; // Perorangan tidak diketahui (relasi eksternal)
        } else {
            $color = '#f15a23'; // Perorangan nasabah
        }
    } elseif ($jenis === 'perusahaan') {
        if (strtolower(trim($data['cif'])) === 'non-nasabah') {
            $color = '#8ee2ed'; // Perusahaan bukan nasabah
        } elseif (empty($data['cif'])) {
            $color = '#ffb0b5'; // Perusahaan tidak diketahui (relasi eksternal)
        } else {
            $color = '#005e6a'; // Perusahaan nasabah
        }
    } else {
        $color = '#ccc'; // Default fallback
    }
}


    $nodes[] = [
        'id' => $id,
        'label' => "$label\n($cif)",
        'color' => $color
    ];

    // === 1. Relasi ke perusahaan dari perorangan ===
    if ($jenis === 'perorangan') {
        $relasiPerusahaan = json_decode($data['nama_perusahaan_hub_perorangan'], true);
        if (is_array($relasiPerusahaan)) {
            foreach ($relasiPerusahaan as $relasi) {
                $namaPerusahaan = trim(strtolower($relasi['nama'] ?? ''));
                $jenisRelasi = $relasi['jenis'] ?? 'Hubungan';
                if (isset($namaMap[$namaPerusahaan])) {
                    $targetId = $namaMap[$namaPerusahaan];
                    $edges[] = [
                        'from' => $id,
                        'to' => $targetId,
                        'label' => $jenisRelasi,
                        'arrows' => 'to'
                    ];
                    telusuri($targetId, $nodes, $edges, $visited, $idMap, $namaMap, $pdo);
                }
            }
        }
    }

    // === 2. Relasi dari perusahaan ke perorangan ===
    if ($jenis === 'perusahaan') {
        $stmt = $pdo->query("SELECT id_perorangan, nama_perusahaan_hub_perorangan FROM nasabah_perorangan");
        foreach ($stmt as $perorangan) {
            $relasi = json_decode($perorangan['nama_perusahaan_hub_perorangan'], true);
            if (is_array($relasi)) {
                foreach ($relasi as $r) {
                    if (trim(strtolower($r['nama'] ?? '')) === trim(strtolower($data['nama_perusahaan']))) {
                        $targetId = 'perorangan_' . $perorangan['id_perorangan'];
                        if (isset($idMap[$targetId])) {
                            $edges[] = [
                                'from' => $targetId,
                                'to' => $id,
                                'label' => $r['jenis'] ?? 'Hubungan',
                                'arrows' => 'to'
                            ];
                            telusuri($targetId, $nodes, $edges, $visited, $idMap, $namaMap, $pdo);
                        }
                    }
                }
            }
        }
    }

    // === 3. Relasi antar perusahaan ===
    if (isset($data['nama_perusahaan_hub'])) {
        $hubunganList = json_decode($data['nama_perusahaan_hub'], true);
        if (is_array($hubunganList)) {
            foreach ($hubunganList as $hubungan) {
                $namaTarget = trim(strtolower($hubungan['nama_perusahaan'] ?? ''));
                $jenisHubungan = $hubungan['jenis_hubungan'] ?? 'Hubungan';
                if (isset($namaMap[$namaTarget])) {
                    $targetId = $namaMap[$namaTarget];
                    $edges[] = [
                        'from' => $id,
                        'to' => $targetId,
                        'label' => $jenisHubungan,
                        'arrows' => 'to'
                    ];
                    telusuri($targetId, $nodes, $edges, $visited, $idMap, $namaMap, $pdo);
                }
            }
        }
    }

    // === 4. Relasi antar perorangan (keluarga) ===
    if ($jenis === 'perorangan' && !empty($data['nama_relasi_hub_perorangan'])) {
        $relasiList = json_decode($data['nama_relasi_hub_perorangan'], true);
        if (is_array($relasiList)) {
            foreach ($relasiList as $relasi) {
                $targetNama = trim(strtolower($relasi['nama'] ?? ''));
                $jenisRelasi = $relasi['jenis'] ?? 'Relasi';

                if (isset($namaMap[$targetNama])) {
                    $targetId = $namaMap[$targetNama];

                    // Hindari duplikasi edge dua arah (optional)
                    $edges[] = [
                        'from' => $id,
                        'to' => $targetId,
                        'label' => $jenisRelasi,
                        'arrows' => 'to'
                    ];

                    telusuri($targetId, $nodes, $edges, $visited, $idMap, $namaMap, $pdo);
                }
            }
        }
    }

}


telusuri($fullId, $nodes, $edges, $visited, $idMap, $namaMap, $pdo);
echo json_encode(['nodes' => $nodes, 'edges' => $edges]);
