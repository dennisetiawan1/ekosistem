<?php
require 'koneksi.php';
session_start();

// Cek login
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Silakan login terlebih dahulu.");
}


// Ambil parameter pencarian
$search = trim($_GET['q'] ?? '');

// Query untuk nasabah_perusahaan
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM nasabah_perusahaan 
        WHERE nama_perusahaan LIKE ? 
        OR cif LIKE ? 
        OR bentuk_perusahaan LIKE ? 
        ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM nasabah_perusahaan ORDER BY created_at DESC");
}
$nasabahList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Jika request via AJAX, kirim hanya bagian <tr>
if (isset($_GET['ajax'])) {
    foreach ($nasabahList as $n) {
        $idn = $n['created_by'];
        $updatedby = 'Pegawai';

        if ($idn) {
            $stmt = $pdo->prepare("SELECT nama_pegawai FROM pegawai WHERE id = ?");
            $stmt->execute([$idn]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $updatedby = $row['nama_pegawai'];
            }
        }
        echo "<tr id='perusahaan_{$n['id']}' onclick='tampilkanInfoNasabahDariServer(this.id)'>
            <td>" . htmlspecialchars($n['cif']) . "</td>
            <td>" . htmlspecialchars($n['nama_perusahaan']) . "</td>
            <td>" . htmlspecialchars($n['bentuk_perusahaan']) . "</td>
            <td>" . htmlspecialchars($n['tahun_nasabah']) . "</td>
            <td>" . htmlspecialchars($n['created_at']) . '<br> oleh: ' . htmlspecialchars($updatedby) . "</td>
            <td data-label='Aksi' class='kolom-btn'>
                <a class='btn-edit' href='form_edit_perusahaan.php?id=" . $n['id'] . "'>
                    <img src='img/edit.svg' alt='' width='16'>
                </a>
                <a class='btn-hapus' onclick='event.stopPropagation(); hapusNasabah(" . $n['id'] . ")'>
                    <img src='img/delete.svg' alt='' width='16'>
                </a>
                <br><br>
                 <a class='btn-ekosistem' href='javascript:void(0);' onclick='event.stopPropagation(); bukaModal(" . $n['id'] . ");'>Ekosistem</a>
            </td>
        </tr>";
    }
    exit;
}

// Ambil daftar produk_lainnya untuk mapping (jika nanti diperlukan)
$produkMap = [];
$stmtProduk = $pdo->query("SELECT id, nama_produk FROM produk_lainnya");
while ($row = $stmtProduk->fetch(PDO::FETCH_ASSOC)) {
    $produkMap[$row['id']] = $row['nama_produk'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Daftar Nasabah Perusahaan</title>
    <link rel="stylesheet" href="<?= $base_url ?>/css/informasi.css">
    <!-- SweetAlert2 CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <base href="<?= $base_url ?>">

</head>

<body>

    <?php include 'sidebar.php' ?>

    <main class="right-content-informasi">
        <h1>Daftar Nasabah Perusahaan</h1>
        <div method="GET" class="search-form">
            <input type="text" id="searchInput" name="q" placeholder="Cari CIF, Nama, atau Bentuk Perusahaan..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
            <img src="img/search-icon.svg" alt="" width="24">
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'cif')">CIF <span id="sort-cif" class="sort-icons"><span class="asc">&#8593;</span><span class="desc">&#8595;</span></span></th>
                        <th onclick="sortTable(1, 'nama')">Nama <span id="sort-nama" class="sort-icons"><span class="asc">&#8593;</span><span class="desc">&#8595;</span></span></th>
                        <th onclick="sortTable(2, 'bentuk')">Bentuk <span id="sort-bentuk" class="sort-icons"><span class="asc">&#8593;</span><span class="desc">&#8595;</span></span></th>
                        <th onclick="sortTable(3, 'tahun')">Tahun <span id="sort-tahun" class="sort-icons"><span class="asc">&#8593;</span><span class="desc">&#8595;</span></span></th>
                        <th onclick="sortTable(4, 'updated')">Terakhir <span id="sort-updated" class="sort-icons"><span class="asc">&#8593;</span><span class="desc">&#8595;</span></span></th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="nasabahTableBody">
                    <?php foreach ($nasabahList as $n): ?>
                        <tr id="perusahaan_<?= $n['id'] ?>" onclick="tampilkanInfoNasabahDariServer(this.id)">
                            <td data-label="CIF"><?= htmlspecialchars($n['cif']) ?></td>
                            <td data-label="Nama Perusahaan"><?= htmlspecialchars($n['nama_perusahaan']) ?></td>
                            <td data-label="Bentuk"><?= htmlspecialchars($n['bentuk_perusahaan']) ?></td>
                            <td data-label="Tahun"><?= htmlspecialchars($n['tahun_nasabah']) ?></td>
                            <td data-label="Terakhir Diubah"><?= htmlspecialchars($n['created_at']) ?>
                                <?php
                                $idn = $n['created_by'];
                                $updatedby = 'Pegawai';
    
                                if ($idn) {
                                    $stmt = $pdo->prepare("SELECT nama_pegawai FROM pegawai WHERE id = ?");
                                    $stmt->execute([$idn]);
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
                                    if ($row) {
                                        $updatedby = $row['nama_pegawai'];
                                    }
                                }
                                echo  '<br> oleh: ' . htmlspecialchars($updatedby)
                                ?>
    
                            </td>
                            <td data-label="Aksi" class="kolom-btn">
                                <a class="btn-edit" href="<?= $base_url . 'edit/perusahaan/' . $n['id'] ?>" onclick="event.stopPropagation();">
                                    <img src="img/edit.svg" alt="" width="16">
                                </a>
                                <a class="btn-hapus" onclick="event.stopPropagation(); hapusNasabah(<?= $n['id'] ?>)">
                                    <img src="img/delete.svg" alt="" width="16">
                                </a>
                                <br><br>
                                <a class="btn-ekosistem" href="javascript:void(0);" onclick="event.stopPropagation(); bukaModal(<?= $n['id'] ?>)">Ekosistem</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>


    <div id="ekosistemModal">
        <div style="padding:10px; text-align:right;">
            <button onclick="tutupModal()" style="padding:5px 10px; background:#f15a23; color:white; border:none; border-radius:4px;">X</button>
        </div>
        <div id="network"></div>
    </div>

    <!-- Panel info kanan -->
    <div id="nodeInfoPanel">
        <button id="nodeInfoPanelClose" onclick="tutupPanelInfo()">Tutup</button>
        <div id="nodeDetailContent">Pilih node untuk melihat detail.</div>
    </div>

    <!-- Loading -->
    <div id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script src="https://unpkg.com/vis-network@9.1.2/dist/vis-network.min.js"></script>
    <link href="https://unpkg.com/vis-network@9.1.2/dist/vis-network.min.css" rel="stylesheet" />

    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        function hitungLevel(nodes, edges) {
            const levelMap = {};
            const inDegree = {};
            const graph = {};

            // Inisialisasi
            nodes.forEach(n => {
                levelMap[n.id] = 0;
                inDegree[n.id] = 0;
                graph[n.id] = [];
            });

            // Bangun graph dan hitung in-degree
            edges.forEach(e => {
                graph[e.from].push(e.to);
                inDegree[e.to]++;
            });

            // Cari node dengan in-degree = 0 (root)
            const queue = [];
            for (let id in inDegree) {
                if (inDegree[id] === 0) {
                    queue.push(id);
                }
            }

            // Proses level
            while (queue.length > 0) {
                const current = queue.shift();
                graph[current].forEach(next => {
                    levelMap[next] = Math.max(levelMap[next], levelMap[current] + 1);
                    inDegree[next]--;
                    if (inDegree[next] === 0) queue.push(next);
                });
            }

            return levelMap;
        }


        document.getElementById('searchInput').addEventListener('input', function() {
            const query = this.value;

            fetch('informasi_perusahaan.php?q=' + encodeURIComponent(query) + '&ajax=1')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('nasabahTableBody').innerHTML = html;
                });
        });

        const baseUrl = window.location.origin + '/BNI%20v12/';

        function bukaModal(id) {
            showLoading();
            fetch(baseUrl + 'ekosistem/' + id + '/perusahaan')
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    const container = document.getElementById('network');
                    const nodes = new vis.DataSet(data.nodes);
                    // Fungsi untuk deteksi apakah ada edge sebaliknya
                    function hasOppositeEdge(edge, edgeList) {
                        return edgeList.some(e => e.from === edge.to && e.to === edge.from);
                    }

                    // Transformasi edges sebelum masuk ke vis.DataSet
                    const adjustedEdges = Array.isArray(data.edges) ? data.edges.map(e => {

                        if (hasOppositeEdge(e, data.edges)) {
                            // Jika ada edge dua arah, bedakan type kelengkungannya
                            return {
                                ...e,
                                smooth: {
                                    type: 'curvedCW',
                                    roundness: 0.5
                                }
                            };
                        } else {
                            // Edge biasa
                            return {
                                ...e,
                                smooth: {
                                    type: 'curvedCCW',
                                    roundness: 0.5
                                }
                            };
                        }
                    }) : [];

                    const edges = new vis.DataSet(adjustedEdges);
                    const network = new vis.Network(container, {
                        nodes,
                        edges
                    }, {
                        layout: {
                            improvedLayout: true,
                            randomSeed: 5 // Bisa diganti angka lain untuk variasi tata letak
                        },
                        // layout: {
                        //     hierarchical: {
                        //         direction: "RL",
                        //         levelSeparation: 300,
                        //         nodeSpacing: 300,
                        //         sortMethod: 'directed'
                        //     }
                        // },
                        edges: {
                            font: {
                                align: 'middle',
                                size: 14,
                                color: '#000'
                            },
                            arrows: {
                                to: {
                                    enabled: true
                                }
                            },

                            smooth: {
                                type: "cubicBezier",
                                roundness: 0.3
                            }
                        },

                        nodes: {
                            shape: "box",
                            margin: 20, // <<< Ubah dari 12 ke 20 atau lebih jika perlu
                            font: {
                                size: 16
                            }
                        },
                        physics: {
                            enabled: false
                        },
                        interaction: {
                            zoomView: true,
                            dragView: true,
                            navigationButtons: false,
                            selectable: true
                        }
                    });

                    network.once('afterDrawing', () => {
                        const nodeId = 'perusahaan_' + id; // gunakan format ID lengkap yang sesuai
                        if (nodes.get(nodeId)) {
                            network.focus(nodeId, {
                                scale: 1.2,
                                animation: {
                                    duration: 1000
                                }
                            });
                            network.selectNodes([nodeId]);
                        }
                    });


                    network.on("click", function(params) {
                        if (params.nodes.length > 0) {
                            const idGabungan = params.nodes[0]; // contoh: 'perorangan_7'
                            const [jenis, id] = idGabungan.split('_'); // pisah
                            network.focus(idGabungan, {
                                scale: 1.2,
                                animation: {
                                    duration: 800,
                                    easingFunction: 'easeInOutQuad'
                                }
                            });
                            console.log(idGabungan)
                            tampilkanInfoNasabahDariServer(idGabungan); // atau kirim terpisah
                            // fetch(`detail.php?id=${id}&jenis=${jenis}`);
                        }
                    });


                    document.getElementById('ekosistemModal').style.display = 'block';
                });
        }

        function tutupModal() {
            document.getElementById('ekosistemModal').style.display = 'none';
            document.getElementById('network').innerHTML = '';
            tutupPanelInfo();
        }

        function tutupModalNasabah() {
            document.getElementById("nasabahModal").style.display = "none";
        }

        function tutupPanelInfo() {
            document.getElementById('nodeInfoPanel').style.right = '-600px';
        }

        function tampilkanInfoNasabahDariServer(idGabungan) {
            const [jenis, id] = idGabungan.split('_'); // pisahkan jadi 'perorangan', '7'

            if (jenis !== 'perusahaan' && jenis !== 'perorangan') {
                document.getElementById('nodeDetailContent').innerHTML = `
            <h2>Informasi tidak tersedia.</h2>
            <p><strong>Data untuk entitas yang Anda pilih belum tersedia.</strong></p>`;
                document.getElementById('nodeInfoPanel').style.right = '0';
                return;
            }
            showLoading();
            fetch(baseUrl + 'detail/' + jenis + '/' + id)
                .then(res => res.json())
                .then(data => {
                    hideLoading();
                    if (data.error) {
                        document.getElementById('nodeDetailContent').innerHTML = `<p>${data.error}</p>`;
                        return;
                    }

                    let html = '';

                    if (jenis === 'perusahaan') {
                        html = `
                    <h3>${data.nama_perusahaan}</h3>
                    <hr><p><strong>CIF:</strong> ${data.cif}</p>
                    <p><strong>Alamat:</strong> ${data.alamat_perusahaan ?? '-'}</p>
                    <p><strong>Bentuk:</strong> ${data.bentuk_perusahaan}</p>
                    <p><strong>Tahun Nasabah:</strong> ${data.tahun_nasabah}</p>
                    <p><strong>BNI Direct:</strong> ${data.bni_direct}</p>
                    <p><strong>Payroll BNI:</strong> ${data.payroll_bni} ${data.jumlah_pegawai_payroll ? `(${data.jumlah_pegawai_payroll} pegawai)` : ''}</p>
                    <hr>
                    <h4>DPK</h4>
                    <ul>
                        <li><strong>Giro:</strong> Rp${formatRupiah(data.dpk_giro)}</li>
                        <li><strong>Tabungan:</strong> Rp${formatRupiah(data.dpk_tabungan)}</li>
                        <li><strong>Deposito:</strong> Rp${formatRupiah(data.dpk_deposito)}</li>
                    </ul>
                    <hr>
                    <h4>Kredit</h4>
                    <ul>
                        <li><strong>Modal Kerja:</strong> Rp${formatRupiah(data.kredit_modal)}</li>
                        <li><strong>Investasi:</strong> Rp${formatRupiah(data.kredit_investasi)}</li>
                        <li><strong>CCC:</strong> Rp${formatRupiah(data.kredit_ccc)}</li>
                    </ul>`

                        if (data.produk_lainnya_detail?.length > 0) {
                            html += `<hr><h4>Produk Lainnya</h4><ul>`;
                            data.produk_lainnya_detail.forEach(p => {
                                html += `<li><strong>${p.nama}:</strong> Rp${formatRupiah(p.jumlah)}</li>`;
                            });
                            html += `</ul>`;
                        }

                        if (data.hubungan?.length > 0) {
                            html += `<hr><h4>Hubungan</h4><ul>`;
                            data.hubungan.forEach(h => {
                                html += `<li><strong>${h.nama_perusahaan} (${h.jenis_hubungan})</strong></li>`;
                            });
                            html += `</ul>`;
                        }

                    } else if (jenis === 'perorangan') {
                        html = `<h3>${data.nama_perorangan}</h3>
                    <hr><p><strong>CIF:</strong> ${data.cif_perorangan}</p>
                    <p><strong>Jenis Kelamin:</strong> ${data.jenis_kelamin ?? '-'}</p>
                    <p><strong>No.Telpon:</strong> ${data.tlpn}</p>
                    <p><strong>Tahun Nasabah:</strong> ${data.tahun_perorangan}</p>
                    <hr>
                    <h4>DPK</h4>
                    <ul>
                    <p>Giro: Rp${formatRupiah(data.dpk_giro_perorangan)}</p>
                    <p>Tabungan: Rp${formatRupiah(data.dpk_tabungan_perorangan)}</p>
                    <p>Deposito: Rp${formatRupiah(data.dpk_deposito_perorangan)}</p>
                    <p>Emerald: Rp${formatRupiah(data.dpk_emerald_perorangan)}</p>
                    </ul>
                    <hr>
                    <h4>Kredit</h4>
                    <ul>
                    <p>Konsumer - Griya: Rp${formatRupiah(data.kredit_konsumer_griya__perorangan)}</p>
                    <p>Konsumer - Fleksi: Rp${formatRupiah(data.kredit_konsumer_fleksi__perorangan)}</p>
                    <p>CC: Rp${formatRupiah(data.kredit_cc_perorangan)}</p>
                    <p>BNI INSTAN: Rp${formatRupiah(data.kredit_bni_instan)}</p>
                    </ul>
                    <hr>
                    <p><strong>Mobile Banking:</strong> ${data.mobile_banking}</p>
                    `;

                        if (data.produk_lainnya_detail?.length > 0) {
                            html += `<hr><h4>Produk Lainnya</h4><ul>`;
                            data.produk_lainnya_detail.forEach(p => {
                                html += `<li>${p.nama}: Rp${formatRupiah(p.jumlah)}</li>`;
                            });
                            html += `</ul>`;
                        }

                        if (data.relasi?.length > 0) {
                            html += `<hr><h4>Relasi Keluarga</h4><ul>`;
                            data.relasi.forEach(r => {
                                html += `<li>${r.nama} (${r.jenis})</li>`;
                            });
                            html += `</ul>`;
                        }

                        if (data.hubungan_perusahaan?.length > 0) {
                            html += `<hr><h4>Hubungan ke Perusahaan</h4><ul>`;
                            data.hubungan_perusahaan.forEach(h => {
                                html += `<li>${h.nama} (${h.jenis})</li>`;
                            });
                            html += `</ul>`;
                        }
                    }

                    document.getElementById('nodeDetailContent').innerHTML = html;
                    document.getElementById('nodeInfoPanel').style.right = '0';
                });
        }


        function formatRupiah(angka) {
            return Number(angka).toLocaleString('id-ID');
        }

        window.addEventListener('beforeunload', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });

        window.addEventListener('pageshow', function(event) {
            // Jika halaman dimuat dari cache, pastikan loading disembunyikan
            if (event.persisted) {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        });

        window.addEventListener('load', function() {
            document.getElementById('loadingOverlay').style.display = 'none';
        });

        function hapusNasabah(id) {
            Swal.fire({
                title: 'Yakin hapus?',
                text: "Data nasabah akan dihapus secara permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    lakukanPenghapusan(id);
                }
            });
        }

        function lakukanPenghapusan(id) {
            showLoading();
            fetch("hapus_perusahaan.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "id=" + encodeURIComponent(id)
                })
                .then(res => res.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Nasabah berhasil dihapus.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.error || 'Gagal menghapus nasabah.',
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Tidak dapat menghubungi server.',
                    });
                });
        }

        let currentSortedColumn = null;
        let currentSortDirection = 'asc';

        function sortTable(colIndex, key) {
            const tbody = document.getElementById("nasabahTableBody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            // Toggle asc/desc jika kolom sama diklik lagi
            if (currentSortedColumn === key) {
                currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortedColumn = key;
                currentSortDirection = 'asc';
            }

            rows.sort((a, b) => {
                let aText = a.children[colIndex].innerText.trim().toLowerCase();
                let bText = b.children[colIndex].innerText.trim().toLowerCase();

                let isNumber = !isNaN(aText) && !isNaN(bText);
                if (isNumber) {
                    aText = parseFloat(aText);
                    bText = parseFloat(bText);
                }

                if (aText < bText) return currentSortDirection === 'asc' ? -1 : 1;
                if (aText > bText) return currentSortDirection === 'asc' ? 1 : -1;
                return 0;
            });

            rows.forEach(row => tbody.appendChild(row));

            updateSortIcons();
        }

        function updateSortIcons() {
            const allIcons = document.querySelectorAll('.sort-icons .asc, .sort-icons .desc');
            allIcons.forEach(icon => icon.classList.remove('active'));

            if (currentSortedColumn) {
                const target = document.querySelector(`#sort-${currentSortedColumn} .${currentSortDirection}`);
                if (target) target.classList.add('active');
            }
        }
    </script>

</body>

</html>