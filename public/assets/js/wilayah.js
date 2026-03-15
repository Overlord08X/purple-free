$(document).ready(function () {
    let dataWilayah = [];

    let namaProvinsi = "";
    let namaKota = "";
    let namaKecamatan = "";
    let namaKelurahan = "";

    // LOAD PROVINSI
    axios.get("/provinsi").then(function (response) {
        let data = response.data;

        data.forEach(function (p) {
            $("#provinsi").append(`<option value="${p.id}">${p.name}</option>`);
        });
    });

    // PROVINSI → KOTA
    $("#provinsi").change(function () {
        let id = $(this).val();

        namaProvinsi = $("#provinsi option:selected").text();

        $("#kota").html("<option value=''>Pilih Kota</option>");
        $("#kecamatan").html("<option value=''>Pilih Kecamatan</option>");
        $("#kelurahan").html("<option value=''>Pilih Kelurahan</option>");

        axios.get("/kota/" + id).then(function (response) {
            let data = response.data;

            data.forEach(function (k) {
                $("#kota").append(`<option value="${k.id}">${k.name}</option>`);
            });
        });
    });

    // KOTA → KECAMATAN
    $("#kota").change(function () {
        let id = $(this).val();

        namaKota = $("#kota option:selected").text();

        $("#kecamatan").html("<option value=''>Pilih Kecamatan</option>");
        $("#kelurahan").html("<option value=''>Pilih Kelurahan</option>");

        axios.get("/kecamatan/" + id).then(function (response) {
            let data = response.data;

            data.forEach(function (k) {
                $("#kecamatan").append(
                    `<option value="${k.id}">${k.name}</option>`,
                );
            });
        });
    });

    // KECAMATAN → KELURAHAN
    $("#kecamatan").change(function () {
        let id = $(this).val();

        namaKecamatan = $("#kecamatan option:selected").text();

        $("#kelurahan").html("<option value=''>Pilih Kelurahan</option>");

        axios.get("/kelurahan/" + id).then(function (response) {
            let data = response.data;

            data.forEach(function (k) {
                $("#kelurahan").append(
                    `<option value="${k.id}">${k.name}</option>`,
                );
            });
        });
    });

    // PILIH KELURAHAN
    $("#kelurahan").change(function () {
        namaKelurahan = $("#kelurahan option:selected").text();

        let wilayah = {
            provinsi: namaProvinsi,
            kota: namaKota,
            kecamatan: namaKecamatan,
            kelurahan: namaKelurahan,
        };

        dataWilayah.push(wilayah);

        renderTable();
    });

    function renderTable() {
        $("#tableWilayah tbody").html("");

        dataWilayah.forEach(function (w, index) {
            $("#tableWilayah tbody").append(`
<tr>
<td>${w.provinsi}</td>
<td>${w.kota}</td>
<td>${w.kecamatan}</td>
<td>${w.kelurahan}</td>
<td>
<button class="btn btn-danger btn-sm hapus" data-index="${index}">
Hapus
</button>
</td>
</tr>
`);
        });
    }

    $("#tableWilayah").on("click", ".hapus", function () {
        let index = $(this).data("index");

        dataWilayah.splice(index, 1);

        renderTable();
    });
});
