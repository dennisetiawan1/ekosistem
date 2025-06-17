<?php
require 'koneksi.php';
session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: " . $base_url . "login");
    exit;
}

$produkList = $pdo->query("SELECT id, nama_produk FROM produk_lainnya")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Form Nasabah Perorangan</title>
    <link rel="stylesheet" href="<?= $base_url. "/css/dashboard.css"?>">
    <link rel="stylesheet" href="<?= $base_url. "/css/form.css"?>">
    <base href="<?= $base_url ?>">
</head>

<body>

    <?php include 'sidebar.php' ?>

    <main class="right-content-form">
        <h1>Form Perorangan</h1>

        <label>Apakah Anda adalah nasabah BNI?</label>
        <select id="status_nasabah" onchange="tampilkanForm()">
            <option value="">-- Pilih --</option>
            <option value="nasabah">Nasabah</option>
            <option value="non_nasabah">Non-Nasabah</option>
        </select>

        <!-- FORM NASABAH -->
        <div id="form_nasabah" class="section">
            <form method="POST" action="<?= $base_url . "simpan/perorangan" ?>">
                <!-- Form lengkap seperti sebelumnya -->
                <label>CIF:</label>
                <input type="text" name="cif_perorangan" required />

                <label>Nama:</label>
                <input type="text" name="nama_perorangan" required />

                <label>Jenis Kelamin:</label>
                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>

                <label>No. Telepon:</label>
                <input type="tel" name="tlpn" placeholder="08xxxxxxxxxx" required>

                <label>Menjadi Nasabah Sejak Tahun:</label>
                <input type="number" name="tahun_perorangan" min="1900" max="2099" required />

                <!-- DPK -->
                <h3>Produk BNI (Rp. Total)</h3>
                <label>DPK</label><br>
                Giro: <input type="number" name="dpk_giro_perorangan" min="0" value="0" /><br>
                Tabungan: <input type="number" name="dpk_tabungan_perorangan" min="0" value="0" /><br>
                Deposito: <input type="number" name="dpk_deposito_perorangan" min="0" value="0" /><br>
                Emerald: <input type="number" name="dpk_emerald_perorangan" min="0" value="0" /><br>

                <label>Kredit</label><br>
                Konsumer-Griya: <input type="number" name="kredit_konsumer_griya__perorangan" min="0" value="0" /><br>
                Konsumer-Fleksi: <input type="number" name="kredit_konsumer_fleksi__perorangan" min="0" value="0" /><br>
                CC: <input type="number" name="kredit_cc_perorangan" min="0" value="0" /><br>
                BNI INSTAN: <input type="number" name="bni_instan" min="0" value="0" /><br>

                <label>Mobile Banking/Wondr:</label>
                <select name="mobile_banking" required>
                    <option value="Tidak">Tidak</option>
                    <option value="Ya">Ya</option>
                </select><br>

                <h3>Produk Lainnya</h3>
                <label>Apakah ada produk lainnya?</label>
                <select id="ada_produk_lainnya" name="ada_produk_lainnya">
                    <option value="Tidak" selected>Tidak</option>
                    <option value="Ya">Ya</option>
                </select>

                <div id="produk_lainnya_section" style="display:none;">
                    <div id="produk_lainnya_container"></div>
                    <button type="button" class="btn-tambah-dropdown" id="btn_tambah_produk" onclick="tambahProduk()">+ Tambah Produk</button>

                </div>

                <h3>Hubungan dengan Nasabah Perorangan BNI</h3>
                <label>Apakah Anda memiliki hubungan keluarga dengan nasabah BNI?</label>
                <select id="ada_hubungan_nasabah" name="ada_hubungan_nasabah">
                    <option value="Tidak" selected>Tidak</option>
                    <option value="Ya">Ya</option>
                </select>

                <div id="hubungan_nasabah_section" style="display:none;">
                    <div id="hubungan_nasabah_container"></div>
                    <button type="button" class="btn-tambah-dropdown" onclick="tambahHubunganNasabahKe('hubungan_nasabah_container')">+ Tambah Hubungan</button>
                </div>

                <h3>Hubungan dengan Nasabah Perusahaan BNI</h3>
                <label>Apakah Anda memiliki hubungan dengan nasabah perusahaan BNI?</label>
                <select id="ada_hubungan_perusahaan" name="ada_hubungan_perusahaan">
                    <option value="Tidak" selected>Tidak</option>
                    <option value="Ya">Ya</option>
                </select>

                <div id="hubungan_section" style="display:none;">
                    <div id="hubungan_container"></div>
                    <button type="button" class="btn-tambah-dropdown" onclick="tambahHubunganKe('hubungan_container')">+ Tambah Hubungan</button>
                </div>

                <input type="hidden" name="status_nasabah" value="nasabah" />

                <br>
                <input type="submit" class="btn-submit-form" value="Simpan" />
            </form>
        </div>

        <!-- FORM NON-NASABAH -->
        <div id="form_non_nasabah" class="section">
            <form method="POST" action="<?= $base_url . "simpan/perorangan" ?>">
                <label>Nama:</label>
                <input type="text" name="nama_perorangan" required />

                <label>Jenis Kelamin:</label>
                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="L">Laki-Laki</option>
                    <option value="P">Perempuan</option>
                </select>

                <label>No. Telepon:</label>
                <input type="tel" name="tlpn" placeholder="08xxxxxxxxxx" required>

                <h3>Hubungan dengan Nasabah Perorangan BNI</h3>
                <label>Apakah Anda memiliki hubungan keluarga dengan nasabah BNI?</label>
                <select id="ada_hubungan_nasabah_non" name="ada_hubungan_nasabah_non">
                    <option value="Tidak" selected>Tidak</option>
                    <option value="Ya">Ya</option>
                </select>

                <div id="hubungan_nasabah_section_non" style="display:none;">
                    <div id="hubungan_nasabah_container_non"></div>
                    <button type="button" class="btn-tambah-dropdown" onclick="tambahHubunganNasabahKe('hubungan_nasabah_container_non')">+ Tambah Hubungan</button>
                </div>

                <h3>Hubungan dengan Nasabah Perusahaan BNI</h3>
                <label>Apakah Anda memiliki hubungan dengan nasabah perusahaan BNI?</label>
                <select id="ada_hubungan_perusahaan_non" name="ada_hubungan_perusahaan_non">
                    <option value="Tidak" selected>Tidak</option>
                    <option value="Ya">Ya</option>
                </select>

                <div id="hubungan_section_non" style="display:none;">
                    <div id="hubungan_container_non"></div>
                    <button type="button" class="btn-tambah-dropdown" onclick="tambahHubunganKe('hubungan_container_non')">+ Tambah Hubungan</button>
                </div>
                <input type="hidden" name="status_nasabah" value="non_nasabah" />

                <br>
                <input type="submit" class="btn-submit-form" value="Simpan" />
            </form>
        </div>

    </main>


    <script>
        function tampilkanForm() {
            const pilihan = document.getElementById('status_nasabah').value;
            document.getElementById('form_nasabah').style.display = pilihan === 'nasabah' ? 'block' : 'none';
            document.getElementById('form_non_nasabah').style.display = pilihan === 'non_nasabah' ? 'block' : 'none';
        }

        // Kirim data produkList ke JS
        const produkList = <?= json_encode($produkList) ?>;
    </script>

    <script src="js/form_perorangan.js"></script>

</body>

</html>