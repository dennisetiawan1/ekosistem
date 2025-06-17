<?php
session_start();
require 'koneksi.php';

if (!isset($_GET['id'])) {
    die("ID perusahaan tidak ditemukan.");
}

$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM nasabah_perorangan WHERE id_perorangan = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data tidak ditemukan.");
}



$produkList = $pdo->query("SELECT id, nama_produk FROM produk_lainnya")->fetchAll(PDO::FETCH_ASSOC);
$produk_lainnya = json_decode($data['produk_lainnya'] ?? '[]', true);
$produk_manual = json_decode($data['lainnya'] ?? '[]', true);
$hubungan_lain = json_decode($data['nama_perusahaan_hub_perorangan'] ?? '[]', true);
$relasi_lain = json_decode($data['nama_relasi_hub_perorangan'] ?? '[]', true);
$is_nasabah = ($data['cif_perorangan'] !== 'Non-Nasabah');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Edit Nasabah Perorangan</title>
    <link rel="stylesheet" href="<?= $base_url. "/css/dashboard.css"?>">
    <link rel="stylesheet" href="<?= $base_url. "/css/form-edit.css"?>">
    <base href="<?= $base_url ?>">
</head>

<body>

    <?php include 'sidebar.php' ?>

    <main class="right-content-form">
        <h1>Form Nasabah Perorangan</h1>

        <label>Apakah Anda ini adalah nasabah?</label>
        <select id="status_nasabah" name="status_nasabah" onchange="tampilkanForm()" required>
            <option value="nasabah" <?= $is_nasabah ? 'selected' : '' ?>>Nasabah</option>
            <option value="non_nasabah" <?= !$is_nasabah ? 'selected' : '' ?>>Non-Nasabah</option>
        </select>

        <!-- Form untuk Nasabah -->
        <div id="form_nasabah" class="section" style="display: none;">
            <form method="POST" id="formEditPerorangan" action="<?= $base_url. "simpan/perorangan"?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id)?>">

                <label>CIF:</label>
                <input type="text" name="cif_perorangan" value="<?= htmlspecialchars($data['cif_perorangan']) ?>" required />

                <label>Nama Nasabah:</label>
                <input type="text" name="nama_perorangan" value="<?= htmlspecialchars($data['nama_perorangan']) ?>" required />

                <label>Jenis Kelamin:</label>
                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach (['L', 'P'] as $val): ?>
                        <option value="<?= $val ?>" <?= $data['jenis_kelamin'] === $val ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>

                <label>No. Telepon:</label>
                <input type="tel" name="tlpn" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($data['tlpn']) ?>" required>

                <label>Menjadi Nasabah Sejak Tahun:</label>
                <input type="number" name="tahun_perorangan" value="<?= $data['tahun_perorangan'] ?>" min="1900" max="2099" required />

                <h3>Produk BNI (Rp. Total)</h3>
                Giro: <input type="number" name="dpk_giro_perorangan" value="<?= $data['dpk_giro_perorangan'] ?>" min="0" /><br>
                Tabungan: <input type="number" name="dpk_tabungan_perorangan" value="<?= $data['dpk_tabungan_perorangan'] ?>" min="0" /><br>
                Deposito: <input type="number" name="dpk_deposito_perorangan" value="<?= $data['dpk_deposito_perorangan'] ?>" min="0" /><br>
                Emerald: <input type="number" name="dpk_emerald_perorangan" value="<?= $data['dpk_emerald_perorangan'] ?>" min="0" /><br>

                <h4>Kredit</h4>
                Konsumer-Griya: <input type="number" name="kredit_konsumer_griya__perorangan" value="<?= $data['kredit_konsumer_griya__perorangan'] ?>" min="0" /><br>
                Konsumer-Fleksi: <input type="number" name="kredit_konsumer_fleksi__perorangan" value="<?= $data['kredit_konsumer_fleksi__perorangan'] ?>" min="0" /><br>
                CC: <input type="number" name="kredit_cc" value="<?= $data['kredit_cc_perorangan'] ?>" min="0" /><br>
                BNI Instan: <input type="number" name="bni_instan" value="<?= $data['kredit_bni_instan'] ?>" min="0" /><br>

                <label>Mobile Banking/Wondr:</label>
                <select name="mobile_banking">
                    <option value="Ya" <?= $data['mobile_banking'] === 'Ya' ? 'selected' : '' ?>>Ya</option>
                    <option value="Tidak" <?= $data['mobile_banking'] === 'Tidak' ? 'selected' : '' ?>>Tidak</option>
                </select>

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

                <h3>Hubungan dengan Nasabah Perorangan BNI</h3>
                <label>Apakah Anda memiliki hubungan keluarga dengan nasabah BNI?</label>
                <select id="ada_hubungan_nasabah" name="ada_hubungan_nasabah">
                    <option value="Tidak" <?= empty($relasi_lain) ? 'selected' : '' ?>>Tidak</option>
                    <option value="Ya" <?= !empty($relasi_lain) ? 'selected' : '' ?>>Ya</option>
                </select>

                <div id="hubungan_nasabah_section" style="display: <?= !empty($relasi_lain) ? 'block' : 'none' ?>;">
                    <div id="hubungan_nasabah_container"></div>
                    <button type="button" class="btn-tambah-dropdown" onclick="tambahHubunganNasabahKe('hubungan_nasabah_container')">+ Tambah Hubungan</button>
                </div>

                <h3>Hubungan dengan Nasabah Perusahaan BNI</h3>
                <label>Apakah Anda memiliki hubungan dengan nasabah perusahaan BNI?</label>
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
            <form method="POST" id="formEditPeroranganNon" action="<?= $base_url. "simpan/perorangan"?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <label>Nama Nasabah:</label>
                <input type="text" name="nama_perorangan" value="<?= htmlspecialchars($data['nama_perorangan']) ?>" required />

                <label>Jenis Kelamin:</label>
                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach (['L', 'P'] as $val): ?>
                        <option value="<?= $val ?>" <?= $data['jenis_kelamin'] === $val ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>

                <label>No. Telepon:</label>
                <input type="tel" name="tlpn" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($data['tlpn']) ?>" required>

                <h3>Hubungan dengan Nasabah Perorangan BNI</h3>
                <label>Apakah anda memiliki hubungan keluarga dengan nasabah BNI?</label>
                <select id="ada_hubungan_nasabah_non" name="ada_hubungan_nasabah_non">
                    <option value="Tidak" <?= empty($relasi_lain) ? 'selected' : '' ?>>Tidak</option>
                    <option value="Ya" <?= !empty($relasi_lain) ? 'selected' : '' ?>>Ya</option>
                </select>

                <div id="hubungan_nasabah_section_non" style="display: <?= !empty($relasi_lain) ? 'block' : 'none' ?>;">
                    <div id="hubungan_nasabah_container_non"></div>
                    <button type="button" class="btn-tambah-dropdown" onclick="tambahHubunganNasabahKe('hubungan_nasabah_container_non')">+ Tambah Hubungan</button>
                </div>

                <h3>Hubungan dengan Nasabah Perusahaan BNI</h3>
                <label>Apakah Anda memiliki hubungan dengan nasabah perusahaan BNI?</label>
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
    <script src="js/form_edit_perorangan.js"></script>
    <script>
        const produkList = <?= json_encode($produkList) ?>;
        const produkLainnyaData = <?= json_encode($produk_lainnya) ?>;
        const produkManualData = <?= json_encode($produk_manual) ?>;
        const hubunganData = <?= json_encode($hubungan_lain) ?>;
        const relasiData = <?= json_encode($relasi_lain) ?>;

        document.addEventListener("DOMContentLoaded", function() {
            // Panggil tampilkanForm() terlebih dahulu
            tampilkanForm();

            // Setup event listeners untuk form nasabah
            setupEventListeners();

            // Load data yang sudah ada
            loadExistingData();
        });

        function setupEventListeners() {
            // Event listener untuk produk lainnya
            document.getElementById('ada_produk_lainnya').addEventListener('change', function() {
                const section = document.getElementById('produk_lainnya_section');
                section.style.display = this.value === 'Ya' ? 'block' : 'none';
                if (this.value === 'Ya' && document.querySelectorAll('#produk_lainnya_container .produk-item').length === 0) {
                    tambahProduk();
                }
            });

            // Event listeners untuk hubungan perusahaan
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

            // Event listeners untuk hubungan nasabah
            document.getElementById('ada_hubungan_nasabah').addEventListener('change', function() {
                const section = document.getElementById('hubungan_nasabah_section');
                const container = document.getElementById('hubungan_nasabah_container');
                section.style.display = this.value === 'Ya' ? 'block' : 'none';
                if (this.value === 'Ya' && container.children.length === 0) {
                    tambahHubunganNasabahKe('hubungan_nasabah_container');
                }
            });

            document.getElementById('ada_hubungan_nasabah_non').addEventListener('change', function() {
                const section = document.getElementById('hubungan_nasabah_section_non');
                const container = document.getElementById('hubungan_nasabah_container_non');
                section.style.display = this.value === 'Ya' ? 'block' : 'none';
                if (this.value === 'Ya' && container.children.length === 0) {
                    tambahHubunganNasabahKe('hubungan_nasabah_container_non');
                }
            });
        }

        function loadExistingData() {
            // Load produk lainnya
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

            // Load hubungan perusahaan
            if (hubunganData.length > 0) {
                hubunganData.forEach(h => {
                    tambahHubunganKe('hubungan_container');
                    let last1 = document.querySelector('#hubungan_container').lastElementChild;
                    last1.querySelector('input[name="nama_perusahaan_hub[]"]').value = h.nama;
                    last1.querySelector('select[name="jenis_hubungan[]"]').value = h.jenis;

                    tambahHubunganKe('hubungan_container_non');
                    let last2 = document.querySelector('#hubungan_container_non').lastElementChild;
                    last2.querySelector('input[name="nama_perusahaan_hub[]"]').value = h.nama;
                    last2.querySelector('select[name="jenis_hubungan[]"]').value = h.jenis;
                });
            }

            // Load hubungan nasabah
            if (relasiData.length > 0) {
                relasiData.forEach(h => {
                    tambahHubunganNasabahKe('hubungan_nasabah_container');
                    let last1 = document.querySelector('#hubungan_nasabah_container').lastElementChild;
                    last1.querySelector('input[name="nama_relasi_hub_perorangan[]"]').value = h.nama;
                    last1.querySelector('select[name="jenis_relasi_perorangan[]"]').value = h.jenis;

                    tambahHubunganNasabahKe('hubungan_nasabah_container_non');
                    let last2 = document.querySelector('#hubungan_nasabah_container_non').lastElementChild;
                    last2.querySelector('input[name="nama_relasi_hub_perorangan[]"]').value = h.nama;
                    last2.querySelector('select[name="jenis_relasi_perorangan[]"]').value = h.jenis;
                });
            }
        }

        function tampilkanForm() {
            const pilihan = document.getElementById('status_nasabah').value;
            document.getElementById('form_nasabah').style.display = pilihan === 'nasabah' ? 'block' : 'none';
            document.getElementById('form_non_nasabah').style.display = pilihan === 'non_nasabah' ? 'block' : 'none';
        }

        // Form submission handlers
        document.getElementById('formEditPerorangan').addEventListener('submit', function(e) {
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
                    this.submit();
                }
            });
        });

        document.getElementById('formEditPeroranganNon').addEventListener('submit', function(e) {
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
                    this.submit();
                }
            });
        });
    </script>
</body>

</html>