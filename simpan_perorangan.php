<?php
session_start();
require 'koneksi.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login.");
}

$user_id = $_SESSION['user_id'];
$id = $_POST['id'] ?? null;

// Cek status nasabah (nasabah atau non-nasabah)
$status_nasabah = $_POST['status_nasabah'] ?? 'nasabah';

// Input umum
$cif = $_POST['cif_perorangan'] ?? '';
$nama = $_POST['nama_perorangan'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$tlpn = $_POST['tlpn'] ?? '';
$tahun = $_POST['tahun_perorangan'] ?? '';


// Default untuk non-nasabah
if ($status_nasabah === 'non_nasabah') {
    $cif = 'Non-Nasabah';
    $tahun = '-';
    $dpk_giro = $dpk_tabungan = $dpk_deposito = $dpk_emerald = 0;
    $kredit_griya = $kredit_fleksi = $kredit_cc = $kredit_bni_instan = 0;
    $mobile_banking = 'Tidak';
    $produk_lainnya_str = null;
} else {
    $dpk_giro = $_POST['dpk_giro_perorangan'] ?? 0;
    $dpk_tabungan = $_POST['dpk_tabungan_perorangan'] ?? 0;
    $dpk_deposito = $_POST['dpk_deposito_perorangan'] ?? 0;
    $dpk_emerald = $_POST['dpk_emerald_perorangan'] ?? 0;
    $kredit_griya = $_POST['kredit_konsumer_griya__perorangan'] ?? 0;
    $kredit_fleksi = $_POST['kredit_konsumer_fleksi__perorangan'] ?? 0;
    $kredit_bni_instan = $_POST['bni_instan'] ?? 0;
    $kredit_cc = $_POST['kredit_cc_perorangan'] ?? 0;
    $mobile_banking = $_POST['mobile_banking'] ?? 'Tidak';



    // Produk lainnya
    $produk_ids = $_POST['produk_lainnya_id'] ?? [];
    $produk_jumlahs = $_POST['produk_lainnya_jumlah'] ?? [];
    $produk_nama_manuals = $_POST['produk_lainnya_nama_manual'] ?? [];

    $produk_lainnya_array = []; // produk dari tabel
    $lainnya_array = [];        // produk manual

    $manualIndex = 0; // index untuk nama_manual

    for ($i = 0; $i < count($produk_ids); $i++) {
        $id_produk = $produk_ids[$i];
        $jumlah = (int) ($produk_jumlahs[$i] ?? 0);

        if ($id_produk === 'lainnya') {
            // Ambil nama dari input manual sesuai urutan
            $nama_manual = trim($produk_nama_manuals[$manualIndex] ?? '');
            $manualIndex++;

            if ($nama_manual !== '' && $jumlah > 0) {
                $lainnya_array[] = [
                    'nama' => $nama_manual,
                    'jumlah' => $jumlah
                ];
            }
        } else {
            $id_int = (int) $id_produk;
            if ($id_int > 0 && $jumlah > 0) {
                $produk_lainnya_array[] = [
                    'id' => $id_int,
                    'jumlah' => $jumlah
                ];
            }
        }
    }



    $produk_lainnya_str = json_encode($produk_lainnya_array);
    $lainnya_str = json_encode($lainnya_array);
}

// Hubungan perusahaan
$nama_hub_array = $_POST['nama_perusahaan_hub'] ?? [];
$jenis_hub_array = $_POST['jenis_hubungan'] ?? [];
$hubungan_array = [];

for ($i = 0; $i < count($nama_hub_array); $i++) {
    $nama_hub = trim($nama_hub_array[$i]);
    $jenis_hub = trim($jenis_hub_array[$i] ?? '');
    if ($nama_hub !== '' && $jenis_hub !== '') {
        $hubungan_array[] = [
            'nama' => $nama_hub,
            'jenis' => $jenis_hub
        ];
    }
}
$hubungan_str = json_encode($hubungan_array);

$nama_hub_nasabah_array = $_POST['nama_relasi_hub_perorangan'] ?? [];
$jenis_hub__nasabah_array = $_POST['jenis_relasi_perorangan'] ?? [];
$hubungan_nasabah_array = [];

for ($i = 0; $i < count($nama_hub_nasabah_array); $i++) {
    $nama_hub_nasabah = trim($nama_hub_nasabah_array[$i]);
    $jenis_hub_nasabah = trim($jenis_hub__nasabah_array[$i] ?? '');
    if ($nama_hub_nasabah !== '' && $jenis_hub_nasabah !== '') {
        $hubungan_nasabah_array[] = [
            'nama' => $nama_hub_nasabah,
            'jenis' => $jenis_hub_nasabah
        ];
    }
}
$hubungan_nasabah_str = json_encode($hubungan_nasabah_array);

// Tentukan apakah INSERT atau UPDATE
if ($id) {
    // UPDATE
    $stmt = $pdo->prepare("
        UPDATE nasabah_perorangan SET
            cif_perorangan = ?, nama_perorangan = ?, jenis_kelamin = ?, tlpn = ?, tahun_perorangan = ?,
            dpk_giro_perorangan = ?, dpk_tabungan_perorangan = ?, dpk_deposito_perorangan = ?,
            dpk_emerald_perorangan = ?, kredit_konsumer_griya__perorangan = ?, kredit_konsumer_fleksi__perorangan = ?,
            kredit_cc_perorangan = ?, kredit_bni_instan = ?, mobile_banking = ?,
            produk_lainnya = ?, lainnya = ?, nama_perusahaan_hub_perorangan = ?, nama_relasi_hub_perorangan = ?,
            created_by = ?
        WHERE id_perorangan = ?
    ");
    $stmt->execute([
        $cif,
        $nama,
        $jenis_kelamin,
        $tlpn,
        $tahun,
        $dpk_giro,
        $dpk_tabungan,
        $dpk_deposito,
        $dpk_emerald,
        $kredit_griya,
        $kredit_fleksi,
        $kredit_cc,
        $kredit_bni_instan,
        $mobile_banking,
        $produk_lainnya_str,
        $lainnya_str,
        $hubungan_str,
        $hubungan_nasabah_str,
        $user_id,
        $id
    ]);
} else {
    // INSERT
    $stmt = $pdo->prepare("
        INSERT INTO nasabah_perorangan (
            cif_perorangan, nama_perorangan, jenis_kelamin, tlpn, tahun_perorangan,
            dpk_giro_perorangan, dpk_tabungan_perorangan, dpk_deposito_perorangan, dpk_emerald_perorangan,
            kredit_konsumer_griya__perorangan, kredit_konsumer_fleksi__perorangan, kredit_cc_perorangan, kredit_bni_instan,
            mobile_banking, produk_lainnya, lainnya, nama_perusahaan_hub_perorangan, nama_relasi_hub_perorangan,
            created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $cif,
        $nama,
        $jenis_kelamin,
        $tlpn,
        $tahun,
        $dpk_giro,
        $dpk_tabungan,
        $dpk_deposito,
        $dpk_emerald,
        $kredit_griya,
        $kredit_fleksi,
        $kredit_cc,
        $kredit_bni_instan,
        $mobile_banking,
        $produk_lainnya_str,
        $lainnya_str,
        $hubungan_str,
        $hubungan_nasabah_str,
        $user_id
    ]);
}
echo $id;
header("Location: " . $base_url . "informasi/perorangan");
exit;
