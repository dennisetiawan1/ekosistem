const produkContainer = document.getElementById("produk_lainnya_container");
const hubunganContainer = document.getElementById("hubungan_container");

function tambahProduk() {
  const jumlahSelectSaatIni = produkContainer.querySelectorAll(
    'select[name="produk_lainnya_id[]"]'
  ).length;

  const div = document.createElement("div");
  div.className = "produk-item";

  const select = document.createElement("select");
  select.name = "produk_lainnya_id[]";
  select.required = true;

  const jumlahInput = document.createElement("input");
  jumlahInput.type = "number";
  jumlahInput.name = "produk_lainnya_jumlah[]";
  jumlahInput.min = 1;
  jumlahInput.required = true;

  const hapusBtn = document.createElement("button");
  hapusBtn.type = "button";
  hapusBtn.className = "btn-hapus";
  hapusBtn.textContent = "Hapus";
  hapusBtn.setAttribute("onclick", "konfirmasiHapusItem(this)");

  div.appendChild(select);
  div.appendChild(document.createTextNode(" Jumlah: "));
  div.appendChild(jumlahInput);
  div.appendChild(hapusBtn);
  produkContainer.appendChild(div);

  updateDropdownOptions();
  select.addEventListener("change", updateDropdownOptions);
  select.addEventListener("change", handleLainnya);
  handleLainnya.call(select);
}

function updateDropdownOptions() {
  const selects = document.querySelectorAll(
    'select[name="produk_lainnya_id[]"]'
  );
  const selectedValues = Array.from(selects)
    .map((select) => select.value)
    .filter((val) => val && val !== "lainnya"); // kecuali "lainnya"

  selects.forEach((select) => {
    const currentValue = select.value;
    select.innerHTML = '<option value="">-- Pilih Produk --</option>';

    produkList.forEach((p) => {
      const isSelected = selectedValues.includes(p.id.toString());
      const isCurrent = p.id.toString() === currentValue;
      if (!isSelected || isCurrent) {
        const opt = document.createElement("option");
        opt.value = p.id;
        opt.textContent = p.nama_produk;
        if (isCurrent) opt.selected = true;
        select.appendChild(opt);
      }
    });

    // Tambahkan opsi Lainnya
    const optLainnya = document.createElement("option");
    optLainnya.value = "lainnya";
    optLainnya.textContent = "Lainnya";
    if (currentValue === "lainnya") optLainnya.selected = true;
    select.appendChild(optLainnya);
  });

  // Hilangkan logika sembunyikan tombol
  // Tidak perlu ubah style display tombol tambah
}

function handleLainnya() {
  let inputNama = this.nextElementSibling?.classList?.contains("input-lainnya")
    ? this.nextElementSibling
    : null;
  if (this.value === "lainnya") {
    if (!inputNama) {
      const input = document.createElement("input");
      input.type = "text";
      input.name = "produk_lainnya_nama_manual[]";
      input.placeholder = "Nama Produk Lainnya";
      input.required = true;
      input.classList.add("input-lainnya");
      this.parentElement.insertBefore(input, this.nextSibling);
    }
  } else {
    if (inputNama) inputNama.remove();
  }
}

function tambahHubunganKe(containerId) {
  const container = document.getElementById(containerId);

  const div = document.createElement("div");
  div.className = "hubungan-item";
  div.style.position = "relative";

  div.innerHTML = `
    <input type="text" name="nama_perusahaan_hub[]" class="nama_hub" placeholder="Nama Perusahaan" autocomplete="off" required />
    <div class="autocomplete_result" style="display:none; position:absolute; background:#fff; border:1px solid #ccc; z-index:999;"></div>
    <select name="jenis_hubungan[]" required>
        <option value="">-- Pilih Jenis Hubungan --</option>
        <option value="Group Usaha">Group Usaha</option>
        <option value="Supplier">Supplier</option>
        <option value="Buyer">Buyer</option>
    </select>
    <button type="button" class="btn-hapus" onclick="konfirmasiHapusItem(this)">Hapus</button>
  `;

  container.appendChild(div);

  const input = div.querySelector(".nama_hub");
  const hasilAuto = div.querySelector(".autocomplete_result");

  input.addEventListener("input", function () {
    const keyword = this.value.trim();
    if (keyword.length < 2) {
      hasilAuto.style.display = "none";
      return;
    }

    fetch("cari_perusahaan.php?term=" + encodeURIComponent(keyword))
      .then((res) => res.json())
      .then((data) => {
        hasilAuto.innerHTML = "";
        if (!data.length) {
          hasilAuto.style.display = "none";
          return;
        }

        data.forEach((namaLengkap) => {
          const divOption = document.createElement("div");
          divOption.textContent = namaLengkap;
          divOption.style.padding = "5px";
          divOption.style.cursor = "pointer";

          divOption.addEventListener("click", () => {
            // Ambil hanya bagian nama sebelum tanda kurung
            const hanyaNama = namaLengkap.split(" (")[0];
            input.value = hanyaNama;
            hasilAuto.style.display = "none";
          });

          hasilAuto.appendChild(divOption);
        });

        hasilAuto.style.width = input.offsetWidth + "px";
        hasilAuto.style.display = "block";
      });
  });

  document.addEventListener("click", function (e) {
    if (!hasilAuto.contains(e.target) && e.target !== input) {
      hasilAuto.style.display = "none";
    }
  });
}

let elemenYangAkanDihapus = null;

function konfirmasiHapusItem(button) {
  Swal.fire({
    title: "Yakin ingin menghapus?",
    text: "Data ini akan dihapus dari form.",
    icon: "warning",
    confirmButtonColor: "#e74c3c",
    cancelButtonColor: "#aaa",
    showCancelButton: true,
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (result.isConfirmed) {
      const item = button.parentElement;
      const container = item.parentElement;
      const containerId = container.id;

      item.remove();

      if (containerId === "produk_lainnya_container") {
        if (container.children.length === 0) {
          const dropdown = document.getElementById("ada_produk_lainnya");
          dropdown.value = "Tidak";
          dropdown.dispatchEvent(new Event("change"));
        } else {
          updateDropdownOptions();
        }
      }

      if (containerId === "hubungan_container") {
        if (container.children.length === 0) {
          const dropdown = document.getElementById("ada_hubungan_perusahaan");
          dropdown.value = "Tidak";
          dropdown.dispatchEvent(new Event("change"));
        }
      }

      if (containerId === "hubungan_container_non") {
        if (container.children.length === 0) {
          const dropdown = document.getElementById(
            "ada_hubungan_perusahaan_non"
          );
          dropdown.value = "Tidak";
          dropdown.dispatchEvent(new Event("change"));
        }
      }

      Swal.fire({
        icon: "success",
        title: "Dihapus",
        text: "Data berhasil dihapus.",
        timer: 1000,
        showConfirmButton: false,
      });
    }
  });
}
