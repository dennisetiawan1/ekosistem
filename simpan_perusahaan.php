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
$cif = $_POST['cif'] ?? '';
$nama = $_POST['nama_perusahaan'] ?? '';
$bentuk = $_POST['bentuk_perusahaan'] ?? '';
$alamat = $_POST['alamat_perusahaan'] ?? '';
$tahun = $_POST['tahun_nasabah'] ?? '';

// Default untuk non-nasabah
if ($status_nasabah === 'non_nasabah') {
    $cif = 'Non-Nasabah';
    $tahun = '-';
    $dpk_giro = $dpk_tabungan = $dpk_deposito = 0;
    $kredit_modal = $kredit_investasi = $kredit_ccc = 0;
    $bni_direct = $payroll = 'Tidak';
    $jumlah_payroll = null;
    $produk_lainnya_str = null;
    $lainnya_str = null;
} else {
    $dpk_giro = $_POST['dpk_giro'] ?? 0;
    $dpk_tabungan = $_POST['dpk_tabungan'] ?? 0;
    $dpk_deposito = $_POST['dpk_deposito'] ?? 0;
    $kredit_modal = $_POST['kredit_modal'] ?? 0;
    $kredit_investasi = $_POST['kredit_investasi'] ?? 0;
    $kredit_ccc = $_POST['kredit_ccc'] ?? 0;
    $bni_direct = $_POST['bni_direct'] ?? 'Tidak';
    $payroll = $_POST['payroll_bni'] ?? 'Tidak';
    $jumlah_payroll = $_POST['jumlah_pegawai_payroll'] ?? null;

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
            'nama_perusahaan' => $nama_hub,
            'jenis_hubungan' => $jenis_hub
        ];
    }
}
$hubungan_str = json_encode($hubungan_array);

// Tentukan apakah INSERT atau UPDATE
if ($id) {
    // UPDATE
    $stmt = $pdo->prepare("
        UPDATE nasabah_perusahaan SET
            cif = ?, nama_perusahaan = ?, bentuk_perusahaan = ?, alamat_perusahaan = ?, tahun_nasabah = ?,
            dpk_giro = ?, dpk_tabungan = ?, dpk_deposito = ?,
            kredit_modal = ?, kredit_investasi = ?, kredit_ccc = ?,
            bni_direct = ?, payroll_bni = ?, jumlah_pegawai_payroll = ?,
            produk_lainnya = ?, lainnya = ?, nama_perusahaan_hub = ?,
            created_by = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $cif,
        $nama,
        $bentuk,
        $alamat,
        $tahun,
        $dpk_giro,
        $dpk_tabungan,
        $dpk_deposito,
        $kredit_modal,
        $kredit_investasi,
        $kredit_ccc,
        $bni_direct,
        $payroll,
        $jumlah_payroll,
        $produk_lainnya_str,
        $lainnya_str,
        $hubungan_str,
        $user_id,
        $id
    ]);
} else {
    // INSERT
    $stmt = $pdo->prepare("
        INSERT INTO nasabah_perusahaan (
            cif, nama_perusahaan, bentuk_perusahaan, alamat_perusahaan, tahun_nasabah,
            dpk_giro, dpk_tabungan, dpk_deposito,
            kredit_modal, kredit_investasi, kredit_ccc,
            bni_direct, payroll_bni, jumlah_pegawai_payroll,
            produk_lainnya, lainnya, nama_perusahaan_hub,
            created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $cif,
        $nama,
        $bentuk,
        $alamat,
        $tahun,
        $dpk_giro,
        $dpk_tabungan,
        $dpk_deposito,
        $kredit_modal,
        $kredit_investasi,
        $kredit_ccc,
        $bni_direct,
        $payroll,
        $jumlah_payroll,
        $produk_lainnya_str,
        $lainnya_str,
        $hubungan_str,
        $user_id
    ]);
}

header("Location: " . $base_url . "informasi/perusahaan");
exit;
