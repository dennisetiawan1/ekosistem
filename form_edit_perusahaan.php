<?php
session_start();
require 'koneksi.php';

if (!isset($_GET['id'])) {
    die("ID perusahaan tidak ditemukan.");
}

$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM nasabah_perusahaan WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data tidak ditemukan.");
}

$produkList = $pdo->query("SELECT id, nama_produk FROM produk_lainnya")->fetchAll(PDO::FETCH_ASSOC);
$produk_lainnya = json_decode($data['produk_lainnya'] ?? '[]', true);
$produk_manual = json_decode($data['lainnya'] ?? '[]', true);
$hubungan_lain = json_decode($data['nama_perusahaan_hub'] ?? '[]', true);
$is_nasabah = ($data['cif'] !== 'Non-Nasabah');

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Edit Perusahaan</title>
    <link rel="stylesheet" href="<?= $base_url. "/css/dashboard.css"?>">
    <link rel="stylesheet" href="<?= $base_url. "/css/form-edit.css"?>">
    <link rel="stylesheet" href="<?= $base_url. "/css/sidebar.css"?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <base href="<?= $base_url ?>">
</head>

<body>

    <?php include 'sidebar.php' ?>
    <!-- Tombol hapus rusak -->

    <main class="right-content-form">
        <h1>Form Perusahaan</h1>

        <label>Apakah perusahaan ini adalah nasabah?</label>
        <select id="status_nasabah" name="status_nasabah" onchange="tampilkanForm()" required>
            <option value="nasabah" <?= $is_nasabah ? 'selected' : '' ?>>Nasabah</option>
            <option value="non_nasabah" <?= !$is_nasabah ? 'selected' : '' ?>>Non-Nasabah</option>
        </select>
        <!-- Form untuk Nasabah -->
        <div id="form_nasabah" class="section" style="display: none;">
            <form id="formEditPerusahaan" method="POST" action="<?= $base_url . "simpan/perusahaan" ?>">
                <input type="hidden" name="id" value="<?= $id ?>">

                <label>CIF:</label>
                <input type="text" name="cif" value="<?= htmlspecialchars($data['cif']) ?>" required />

                <label>Nama Perusahaan:</label>
                <input type="text" name="nama_perusahaan" value="<?= htmlspecialchars($data['nama_perusahaan']) ?>" required />

                <label>Bentuk Perusahaan:</label>
                <select name="bentuk_perusahaan" required>
                    <option value="">-- Pilih Bentuk --</option>
                    <?php foreach (['CV', 'PT', 'Lainnya'] as $val): ?>
                        <option value="<?= $val ?>" <?= $data['bentuk_perusahaan'] === $val ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Alamat Perusahaan:</label>
                <input type="text" name="alamat_perusahaan" value="<?= htmlspecialchars($data['alamat_perusahaan']) ?>" required />

                <label>Menjadi Nasabah Sejak Tahun:</label>
                <input type="number" name="tahun_nasabah" value="<?= $data['tahun_nasabah'] ?>" min="1900" max="2099" required />

                <h3>Produk BNI (Rp. Total)</h3>
                Giro: <input type="number" name="dpk_giro" value="<?= $data['dpk_giro'] ?>" min="0" /><br>
                Tabungan: <input type="number" name="dpk_tabungan" value="<?= $data['dpk_tabungan'] ?>" min="0" /><br>
                Deposito: <input type="number" name="dpk_deposito" value="<?= $data['dpk_deposito'] ?>" min="0" /><br>

                <h4>Kredit</h4>
                Modal Kerja: <input type="number" name="kredit_modal" value="<?= $data['kredit_modal'] ?>" min="0" /><br>
                Investasi: <input type="number" name="kredit_investasi" value="<?= $data['kredit_investasi'] ?>" min="0" /><br>
                CCC: <input type="number" name="kredit_ccc" value="<?= $data['kredit_ccc'] ?>" min="0" /><br>

                <label>BNI Direct:</label>
                <select name="bni_direct">
                    <option value="Ya" <?= $data['bni_direct'] === 'Ya' ? 'selected' : '' ?>>Ya</option>
                    <option value="Tidak" <?= $data['bni_direct'] === 'Tidak' ? 'selected' : '' ?>>Tidak</option>
                </select>

                <label>Payroll BNI:</label>
                <select id="payroll_bni" name="payroll_bni">
                    <option value="Tidak" <?= $data['payroll_bni'] === 'Tidak' ? 'selected' : '' ?>>Tidak</option>
                    <option value="Ya" <?= $data['payroll_bni'] === 'Ya' ? 'selected' : '' ?>>Ya</option>
                </select>

                <div id="payroll_detail" style="display: <?= $data['payroll_bni'] === 'Ya' ? 'block' : 'none' ?>;">
                    <label>Jumlah Pegawai Payroll:</label>
                    <input type="number" name="jumlah_pegawai_payroll" value="<?= $data['jumlah_pegawai_payroll'] ?>" />
                </div>

                <h3>Produk Lainnya</h3>
                <label>Apakah ada produk lainnya?</label>
                <select id="ada_produk_lainnya" name="ada_produk_lainnya">
                    <option value="Tidak" <?= empty($produk_lainnya) && empty($produk_manual) ? 'selected' : '' ?>>Tidak</option>
                    <option value="Ya" <?= !empty($produk_lainnya) || !empty($produk_manual) ? 'selected' : '' ?>>Ya</option>
                </select>

                <div id="produk_lainnya_section" style="display: <?= !empty($produk_lainnya) || !empty($produk_manual) ? 'block' : 'none' ?>;">
                    <div id="produk_lainnya_container"></div>
                    <button type="button" class="btn-tambah-dropdown" id="btn_tambah_produk" onclick="tambahProduk()">+ Tambah Produk</button>
                </div>

                <h3>Hubungan dengan Perusahaan Lain</h3>
                <label>Apakah perusahaan memiliki hubungan dengan perusahaan lain?</label>
                <select id="ada_hubungan_perusahaan" name="ada_hubungan_perusahaan">
                    <option value="Tidak" <?= empty($hubungan_lain) ? 'selected' : '' ?>>Tidak</option>
                    <option value="Ya" <?= !empty($hubungan_lain) ? 'selected' : '' ?>>Ya</option>
                </select>

                <div id="hubungan_section" style="display: <?= !empty($hubungan_lain) ? 'block' : 'none' ?>;">
                    <div id="hubungan_container"></div>
                    <button type="button" class="btn-tambah-dropdown" onclick="tambahHubunganKe('hubungan_container')">+ Tambah Hubungan</button>
                </div>

                <br>

                <input type="hidden" name="status_nasabah" value="nasabah" />
                <input type="submit" class="btn-submit-form" value="Simpan" />


            </form>
        </div>

        <!-- Form untuk Non-Nasabah -->
        <div id="form_non_nasabah" class="section" style="display: none;">
            <form id="formEditPerusahaanNon" method="POST" action="<?= $base_url . "simpan/perusahaan" ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <label>Nama Perusahaan:</label>
                <input type="text" name="nama_perusahaan" value="<?= htmlspecialchars($data['nama_perusahaan']) ?>" required />

                <label>Bentuk Perusahaan:</label>
                <select name="bentuk_perusahaan" required>
                    <option value="">-- Pilih Bentuk --</option>
                    <?php foreach (['CV', 'PT', 'Lainnya'] as $val): ?>
                        <option value="<?= $val ?>" <?= $data['bentuk_perusahaan'] === $val ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Alamat Perusahaan:</label>
                <input type="text" name="alamat_perusahaan" value="<?= htmlspecialchars($data['alamat_perusahaan']) ?>" required />

                <h3>Hubungan dengan Perusahaan Lain</h3>
                <label>Apakah perusahaan memiliki hubungan dengan perusahaan lain?</label>
                <select id="ada_hubungan_perusahaan_non" name="ada_hubungan_perusahaan_non">
                    <option value="Tidak" <?= empty($hubungan_lain) ? 'selected' : '' ?>>Tidak</option>
                    <option value="Ya" <?= !empty($hubungan_lain) ? 'selected' : '' ?>>Ya</option>
                </select>

                <div id="hubungan_section_non" style="display: <?= !empty($hubungan_lain) ? 'block' : 'none' ?>;">
                    <div id="hubungan_container_non"></div>
                    <button type="button" class="btn-tambah-dropdown" onclick="tambahHubunganKe('hubungan_container_non')">+ Tambah Hubungan</button>
                </div>
                <br>
                <input type="hidden" name="status_nasabah" value="non_nasabah" />
                <input type="submit" class="btn-submit-form" value="Simpan" />

            </form>
        </div>
    </main>

    <script>
        const produkList = <?= json_encode($produkList) ?>;
        const produkLainnyaData = <?= json_encode($produk_lainnya) ?>;
        const produkManualData = <?= json_encode($produk_manual) ?>;
        const hubunganData = <?= json_encode($hubungan_lain) ?>;
        document.addEventListener("DOMContentLoaded", function() {
            // beri jeda agar HTML rendering selesai dan value <select> sudah terisi
            setTimeout(() => {
                updateDropdownOptions();
            }, 0); // bisa juga 50ms kalau perlu
        });

        document.getElementById('formEditPerusahaan').addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Konfirmasi Ubah Data',
                text: "Apakah Anda yakin ingin mengubah Data?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Ubah',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // lanjut submit
                }
            });
        });
        document.getElementById('formEditPerusahaanNon').addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Konfirmasi Ubah Data',
                text: "Apakah Anda yakin ingin mengubah Data?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Ubah',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // lanjut submit
                }
            });
        });
    </script>
    <script src="js/form_edit_perusahaan.js"></script>

    <script>
        const pilihanUser = document.getElementById('status_nasabah').value;

        function tampilkanForm() {
            const pilihan = document.getElementById('status_nasabah').value;
            document.getElementById('form_nasabah').style.display = pilihan === 'nasabah' ? 'block' : 'none';
            document.getElementById('form_non_nasabah').style.display = pilihan === 'non_nasabah' ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            tampilkanForm();

            document.getElementById('payroll_bni').addEventListener('change', function() {
                document.getElementById('payroll_detail').style.display = this.value === 'Ya' ? 'block' : 'none';
            });


            document.getElementById('ada_produk_lainnya').addEventListener('change', function() {
                const section = document.getElementById('produk_lainnya_section');
                const container = document.getElementById('produk_lainnya_container');
                section.style.display = this.value === 'Ya' ? 'block' : 'none';
                if (this.value === 'Ya' && container.children.length === 0) {
                    tambahProduk();
                }
            });

            document.getElementById('ada_hubungan_perusahaan').addEventListener('change', function() {
                const section = document.getElementById('hubungan_section');
                const container = document.getElementById('hubungan_container');
                section.style.display = this.value === 'Ya' ? 'block' : 'none';
                if (this.value === 'Ya' && container.children.length === 0) {
                    tambahHubunganKe('hubungan_container');
                }
            });

            document.getElementById('ada_hubungan_perusahaan_non').addEventListener('change', function() {
                const section = document.getElementById('hubungan_section_non');
                const container = document.getElementById('hubungan_container_non');
                section.style.display = this.value === 'Ya' ? 'block' : 'none';
                if (this.value === 'Ya' && container.children.length === 0) {
                    tambahHubunganKe('hubungan_container_non');
                }
            });


            [...produkLainnyaData, ...produkManualData.map(p => ({
                id: 'lainnya',
                nama: p.nama,
                jumlah: p.jumlah
            }))].forEach(item => {
                tambahProduk();
                const last = document.querySelector('#produk_lainnya_container').lastElementChild;
                const select = last.querySelector('select');
                const jumlah = last.querySelector('input[type="number"]');
                select.value = item.id;
                select.dispatchEvent(new Event('change'));
                if (item.id === 'lainnya') {
                    const inputManual = last.querySelector('input[name="produk_lainnya_nama_manual[]"]');
                    if (inputManual) inputManual.value = item.nama;
                }
                jumlah.value = item.jumlah;
            });

            if (hubunganData.length > 0) {
                hubunganData.forEach(h => {
                    tambahHubunganKe('hubungan_container');
                    let last1 = document.querySelector('#hubungan_container').lastElementChild;
                    last1.querySelector('input[name="nama_perusahaan_hub[]"]').value = h.nama_perusahaan;
                    last1.querySelector('select[name="jenis_hubungan[]"]').value = h.jenis_hubungan;

                    tambahHubunganKe('hubungan_container_non');
                    let last2 = document.querySelector('#hubungan_container_non').lastElementChild;
                    last2.querySelector('input[name="nama_perusahaan_hub[]"]').value = h.nama_perusahaan;
                    last2.querySelector('select[name="jenis_hubungan[]"]').value = h.jenis_hubungan;
                });
            }

        });



        window.addEventListener('beforeunload', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        });
    </script>

</body>

</html>