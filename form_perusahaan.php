<?php
session_start();
require 'koneksi.php';

$produkList = $pdo->query("SELECT id, nama_produk FROM produk_lainnya")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Form Nasabah Perusahaan</title>
    <link rel="stylesheet" href="<?= $base_url. "/css/dashboard.css"?>">
    <link rel="stylesheet" href="<?= $base_url. "/css/form.css"?>">
    <link rel="stylesheet" href="<?= $base_url. "/css/sidebar.css"?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <base href="<?= $base_url ?>">
</head>

<body>

    <?php include 'sidebar.php' ?>

    <main class="right-content-form">
        <h1>Form Perusahaan</h1>

        <label>Apakah perusahaan ini adalah nasabah?</label>
        <br>
        <select id="status_nasabah" onchange="tampilkanForm()">
            <option value="">-- Pilih --</option>
            <option value="nasabah">Nasabah</option>
            <option value="non_nasabah">Non-Nasabah</option>
        </select>

        <!-- FORM NASABAH -->
        <div id="form_nasabah" class="section">
            <form method="POST" action="<?= $base_url . "simpan/perusahaan" ?>">
                <!-- Form lengkap seperti sebelumnya -->
                <label>CIF:</label>
                <input type="text" name="cif" required />

                <label>Nama Perusahaan:</label>
                <input type="text" name="nama_perusahaan" required />

                <label>Bentuk Perusahaan:</label>
                <select name="bentuk_perusahaan" required>
                    <option value="">-- Pilih Bentuk --</option>
                    <option value="CV">CV</option>
                    <option value="PT">PT</option>
                    <option value="Lainnya">Lainnya</option>
                </select>

                <label>Alamat Perusahaan:</label>
                <input type="text" name="alamat_perusahaan" required />

                <label>Menjadi Nasabah Sejak Tahun:</label>
                <input type="number" name="tahun_nasabah" min="1900" max="2099" required />

                <!-- DPK -->
                <h3>Produk BNI (Rp. Total)</h3>
                <label>DPK</label><br>
                Giro: <input type="number" name="dpk_giro" min="0" value="0" /><br>
                Tabungan: <input type="number" name="dpk_tabungan" min="0" value="0" /><br>
                Deposito: <input type="number" name="dpk_deposito" min="0" value="0" /><br>

                <label>Kredit</label><br>
                Modal Kerja: <input type="number" name="kredit_modal" min="0" value="0" /><br>
                Investasi: <input type="number" name="kredit_investasi" min="0" value="0" /><br>
                CCC: <input type="number" name="kredit_ccc" min="0" value="0" /><br>

                <label>BNI Direct:</label>
                <select name="bni_direct" required>
                    <option value="Tidak">Tidak</option>
                    <option value="Ya">Ya</option>
                </select><br>

                <label>Payroll BNI:</label>
                <select id="payroll_bni" name="payroll_bni" required>
                    <option value="Tidak">Tidak</option>
                    <option value="Ya">Ya</option>
                </select><br>

                <div id="payroll_detail" style="display:none;">
                    <label>Jumlah Pegawai Payroll:</label>
                    <input type="number" name="jumlah_pegawai_payroll" min="1" />
                </div>

                <h3>Produk Lainnya</h3>
                <label>Apakah ada produk lainnya?</label>
                <select id="ada_produk_lainnya" name="ada_produk_lainnya">
                    <option value="Tidak" selected>Tidak</option>
                    <option value="Ya">Ya</option>
                </select>

                <div id="produk_lainnya_section" style="display:none;">
                    <div id="produk_lainnya_container"></div>
                    <button type="button" class="btn-tambah-dropdown" onclick="tambahProduk()">+ Tambah Produk</button>
                </div>

                <h3>Hubungan dengan Perusahaan Lain</h3>
                <label>Apakah perusahaan memiliki hubungan dengan perusahaan lain?</label>
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
            <form method="POST" action="<?= $base_url . "simpan/perusahaan" ?>">
                <label>Nama Perusahaan:</label>
                <input type="text" name="nama_perusahaan" required />

                <label>Bentuk Perusahaan:</label>
                <select name="bentuk_perusahaan" required>
                    <option value="">-- Pilih Bentuk --</option>
                    <option value="CV">CV</option>
                    <option value="PT">PT</option>
                    <option value="Lainnya">Lainnya</option>
                </select>

                <label>Alamat Perusahaan:</label>
                <input type="text" name="alamat_perusahaan" required />

                <h3>Hubungan dengan Perusahaan Lain</h3>
                <label>Apakah perusahaan memiliki hubungan dengan perusahaan lain?</label>
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

        // Tampilkan loading saat halaman mulai dimuat
        window.addEventListener('beforeunload', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });

        // Sembunyikan loading setelah halaman selesai dimuat
        window.addEventListener('load', function() {
            document.getElementById('loadingOverlay').style.display = 'none';
        });
    </script>

    <script src="js/form_perusahaan.js"></script>

</body>

</html>