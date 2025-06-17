const produkContainer = document.getElementById("produk_lainnya_container");
const hubunganContainer = document.getElementById("hubungan_container");
const hubunganNasabahContainer = document.getElementById(
  "hubungan_nasabah_container"
);

function tambahProduk() {
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

  // Set isi dropdown dan update ketika dropdown diubah
  updateDropdownOptions();
  select.addEventListener("change", updateDropdownOptions);
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

  // Tampilkan input manual jika pilih "Lainnya"
  selects.forEach((select) => {
    select.removeEventListener("change", handleLainnya);
    select.addEventListener("change", handleLainnya);
    handleLainnya.call(select); // trigger langsung saat render
  });
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
        <div class="autocomplete_result" style="display:none;"></div>
        <select name="jenis_hubungan[]" required>
            <option value="">-- Pilih Jenis Hubungan --</option>
            <option value="Pemegang Saham">Pemegang Saham</option>
            <option value="Komisaris">Komisaris</option>
            <option value="Direktur">Direktur</option>
            <option value="Karyawan">Karyawan</option>
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
        if (!data.length) {
          hasilAuto.style.display = "none";
          return;
        }

        hasilAuto.innerHTML = "";
        data.forEach((nama) => {
          const divOption = document.createElement("div");
          divOption.textContent = nama;
          divOption.addEventListener("click", () => {
            input.value = nama;
            hasilAuto.style.display = "none";
          });
          hasilAuto.appendChild(divOption);
        });

        const rect = input.getBoundingClientRect();
        hasilAuto.style.width = rect.width + "px";
        hasilAuto.style.display = "block";
      });
  });

  document.addEventListener("click", function (e) {
    if (!hasilAuto.contains(e.target) && e.target !== input) {
      hasilAuto.style.display = "none";
    }
  });
}

function tambahHubunganNasabahKe(containerId) {
  const container = document.getElementById(containerId);

  const div = document.createElement("div");
  div.className = "hubungan-item";
  div.style.position = "relative";

  div.innerHTML = `
        <input type="text" name="nama_relasi_hub_perorangan[]" class="nama_hub" placeholder="Nama Nasabah" autocomplete="off" required />
        <div class="autocomplete_result" style="display:none;"></div>
        <select name="jenis_relasi_perorangan[]" required>
            <option value="">-- Pilih Jenis Hubungan --</option>
            <option value="Bapak">Ayah</option>
            <option value="Ibu">Ibu</option>
            <option value="Anak">Anak</option>
            <option value="Saudara">Saudara</option>
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

    fetch("cari_nasabah.php?term=" + encodeURIComponent(keyword))
      .then((res) => res.json())
      .then((data) => {
        if (!data.length) {
          hasilAuto.style.display = "none";
          return;
        }

        hasilAuto.innerHTML = "";
        data.forEach((namaLengkap) => {
          const divOption = document.createElement("div");
          divOption.textContent = namaLengkap;
          divOption.style.padding = "5px";
          divOption.style.cursor = "pointer";

          divOption.addEventListener("click", () => {
            const hanyaNama = namaLengkap.split(" (")[0];
            input.value = hanyaNama;
            hasilAuto.style.display = "none";
          });

          hasilAuto.appendChild(divOption);
        });

        const rect = input.getBoundingClientRect();
        hasilAuto.style.width = rect.width + "px";
        hasilAuto.style.display = "block";
      });
  });

  document.addEventListener("click", function (e) {
    if (!hasilAuto.contains(e.target) && e.target !== input) {
      hasilAuto.style.display = "none";
    }
  });
}

// Toggle Produk Lainnya
document
  .getElementById("ada_produk_lainnya")
  .addEventListener("change", function () {
    const section = document.getElementById("produk_lainnya_section");
    if (this.value === "Ya") {
      section.style.display = "block";
      if (produkContainer.children.length === 0) tambahProduk();
    } else {
      section.style.display = "none";
      produkContainer.innerHTML = "";
    }
  });

// Toggle Hubungan Perusahaan
document
  .getElementById("ada_hubungan_perusahaan")
  .addEventListener("change", function () {
    const section = document.getElementById("hubungan_section");
    const container = document.getElementById("hubungan_container");

    if (this.value === "Ya") {
      section.style.display = "block";
      if (container.children.length === 0)
        tambahHubunganKe("hubungan_container");
    } else {
      section.style.display = "none";
      container.innerHTML = "";
    }
  });

document
  .getElementById("ada_hubungan_nasabah")
  .addEventListener("change", function () {
    const section = document.getElementById("hubungan_nasabah_section");
    const container = document.getElementById("hubungan_nasabah_container");

    if (this.value === "Ya") {
      section.style.display = "block";
      if (container.children.length === 0)
        tambahHubunganNasabahKe("hubungan_nasabah_container");
    } else {
      section.style.display = "none";
      container.innerHTML = "";
    }
  });

document
  .getElementById("ada_hubungan_perusahaan_non")
  .addEventListener("change", function () {
    const section = document.getElementById("hubungan_section_non");
    const container = document.getElementById("hubungan_container_non");

    if (this.value === "Ya") {
      section.style.display = "block";
      if (container.children.length === 0)
        tambahHubunganKe("hubungan_container_non");
    } else {
      section.style.display = "none";
      container.innerHTML = "";
    }
  });

document
  .getElementById("ada_hubungan_nasabah_non")
  .addEventListener("change", function () {
    const section = document.getElementById("hubungan_nasabah_section_non");
    const container = document.getElementById("hubungan_nasabah_container_non");

    if (this.value === "Ya") {
      section.style.display = "block";
      if (container.children.length === 0)
        tambahHubunganNasabahKe("hubungan_nasabah_container_non");
    } else {
      section.style.display = "none";
      container.innerHTML = "";
    }
  });

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

      if (containerId === "hubungan_nasabah_container") {
        if (container.children.length === 0) {
          const dropdown = document.getElementById("ada_hubungan_nasabah");
          dropdown.value = "Tidak";
          dropdown.dispatchEvent(new Event("change"));
        }
      }

      if (containerId === "hubungan_nasabah_container_non") {
        if (container.children.length === 0) {
          const dropdown = document.getElementById("ada_hubungan_nasabah_non");
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
