let form = document.getElementById("formBarang");
let btnSubmit = document.getElementById("btnSubmit");

let editRow = null;
let table;

// format rupiah
function formatRupiah(angka) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(angka);
}

// inisialisasi datatables
$(document).ready(function () {
    table = $("#tableBarang").DataTable();

    // event klik row
    $("#tableBarang tbody").on("click", "tr", function () {
        editRow = table.row(this);

        let data = editRow.data();

        document.getElementById("editId").value = data[0];
        document.getElementById("editNama").value = data[1];
        document.getElementById("editHarga").value = data[2].replace(/[^\d]/g, "");

        new bootstrap.Modal(document.getElementById("modalEdit")).show();
    });
});

btnSubmit.addEventListener("click", function () {

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    let nama = document.getElementById("namaBarang").value.trim();
    let harga = document.getElementById("hargaBarang").value.trim();

    if (nama === "" || harga === "") {
        return;
    }

    btnSubmit.disabled = true;

    btnSubmit.innerHTML = `
<span class="spinner-border spinner-border-sm"></span> Loading...
`;

    setTimeout(function () {

        let id = Date.now();

        let hargaFormat = formatRupiah(harga);

        // tambah ke datatable
        table.row.add([
            id,
            nama,
            hargaFormat
        ]).draw();

        document.getElementById("namaBarang").value = "";
        document.getElementById("hargaBarang").value = "";

        btnSubmit.innerHTML = "Submit";
        btnSubmit.disabled = false;

    }, 1000);
});


// hapus
document.getElementById("btnDelete").addEventListener("click", function () {

    if (editRow) {
        editRow.remove().draw();
    }

    bootstrap.Modal.getInstance(document.getElementById("modalEdit")).hide();
});


// update
document.getElementById("btnUpdate").addEventListener("click", function () {

    let nama = document.getElementById("editNama").value.trim();
    let harga = document.getElementById("editHarga").value.trim();

    if (nama === "" || harga === "") {
        alert("Data harus diisi");
        return;
    }

    let id = document.getElementById("editId").value;

    editRow.data([
        id,
        nama,
        formatRupiah(harga)
    ]).draw();

    bootstrap.Modal.getInstance(document.getElementById("modalEdit")).hide();
});