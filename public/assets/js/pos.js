$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

let barangFound = false;

$("#btnTambah").prop("disabled", true);

function rupiah(n) {
    return new Intl.NumberFormat("id-ID").format(n);
}

// PILIH BARANG DARI TABEL
$("#tableBarang").on("click", ".pilih-barang", function () {
    let kode = $(this).data("kode");
    let nama = $(this).data("nama");
    let harga = $(this).data("harga");

    $("#idBarang").val(kode);
    $("#namaBarang").val(nama);
    $("#hargaBarang").val(harga);
    $("#jumlah").val(1);

    $("#btnTambah").prop("disabled", false);
});

// AUTO CARI BARANG
$("#idBarang").keypress(function (e) {
    if (e.which == 13) {
        let kode = $(this).val();

        $.get("/barang/" + kode, function (data) {
            if (data) {
                $("#namaBarang").val(data.nama_barang);
                $("#hargaBarang").val(data.harga_barang);
                $("#jumlah").val(1);

                $("#btnTambah").prop("disabled", false);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Barang tidak ditemukan",
                });
            }
        });
    }
});

// TAMBAH BARANG
$("#btnTambah").click(function () {
    $("#spinnerTambah").show();
    $("#textTambah").text("Menambahkan...");
    $("#btnTambah").prop("disabled", true);

    setTimeout(function () {
        let kode = $("#idBarang").val();
        let nama = $("#namaBarang").val();
        let harga = parseInt($("#hargaBarang").val());
        let jumlah = parseInt($("#jumlah").val());

        if (jumlah <= 0) {
            Swal.fire({
                icon: "warning",
                title: "Jumlah harus lebih dari 0",
            });

            $("#spinnerTambah").hide();
            $("#textTambah").text("Tambahkan");
            $("#btnTambah").prop("disabled", false);

            return;
        }

        let subtotal = harga * jumlah;
        let exist = false;

        $("#tablePOS tbody tr").each(function () {
            if ($(this).find("td:eq(0)").text() == kode) {
                let j = parseInt($(this).find(".jumlah").val());
                j += jumlah;

                $(this).find(".jumlah").val(j);
                $(this)
                    .find(".subtotal")
                    .text(rupiah(j * harga));

                exist = true;
            }
        });

        if (!exist) {
            $("#tablePOS tbody").append(`
            <tr>
                <td>${kode}</td>
                <td>${nama}</td>
                <td>${rupiah(harga)}</td>
                <td>
                    <input type="number" class="form-control jumlah" value="${jumlah}" style="width:80px">
                </td>
                <td class="subtotal">${rupiah(subtotal)}</td>
                <td>
                    <button class="btn btn-danger btn-sm hapus">Hapus</button>
                </td>
            </tr>
            `);
        }

        updateTotal();
        clearInput();

        $("#spinnerTambah").hide();
        $("#textTambah").text("Tambahkan");
        $("#btnTambah").prop("disabled", false);
    }, 300);
});

// UPDATE JUMLAH
$("#tablePOS").on("change", ".jumlah", function () {
    let row = $(this).closest("tr");

    let harga = parseInt(row.find("td:eq(2)").text().replace(/\./g, ""));
    let jumlah = parseInt($(this).val());

    let subtotal = harga * jumlah;

    row.find(".subtotal").text(rupiah(subtotal));

    updateTotal();
});

// HAPUS
$("#tablePOS").on("click", ".hapus", function () {
    $(this).closest("tr").remove();

    updateTotal();
});

// HITUNG TOTAL
function updateTotal() {
    let total = 0;

    $("#tablePOS tbody tr").each(function () {
        let s = $(this).find(".subtotal").text();

        s = parseInt(s.replace(/\./g, ""));

        total += s;
    });

    $("#total").text(rupiah(total));
}

// CLEAR INPUT
function clearInput() {
    $("#idBarang").val("");
    $("#namaBarang").val("");
    $("#hargaBarang").val("");
    $("#jumlah").val(1);

    $("#btnTambah").prop("disabled", true);
}

// BAYAR
$("#btnBayar").click(function () {
    $("#spinnerBayarAxios").show();
    $("#textBayarAxios").text("Memproses...");
    $("#btnBayar").prop("disabled", true);

    let items = [];

    $("#tablePOS tbody tr").each(function () {
        items.push({
            kode: $(this).find("td:eq(0)").text(),
            jumlah: $(this).find(".jumlah").val(),
            subtotal: $(this).find(".subtotal").text().replace(/\./g, ""),
        });
    });

    if (items.length == 0) {
        Swal.fire("Tidak ada transaksi");

        $("#spinnerBayarAxios").hide();
        $("#textBayarAxios").html('<i class="mdi mdi-cash"></i> Bayar');
        $("#btnBayar").prop("disabled", false);

        return;
    }

    axios
        .post("/transaksi", {
            items: items,
        })

        .then(function (response) {
            if (response.data.redirect) {
                window.location.href = response.data.redirect;
            } else {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: "Transaksi berhasil disimpan",
                });

                $("#tablePOS tbody").html("");

                updateTotal();
                clearInput();
            }
        })

        .catch(function () {
            Swal.fire("Terjadi kesalahan");
        })

        .finally(function () {
            $("#spinnerBayarAxios").hide();
            $("#textBayarAxios").html(
                '<i class="mdi mdi-cash"></i> Bayar (Axios)',
            );
            $("#btnBayar").prop("disabled", false);
        });
});

// BAYAR Ajax
$("#btnBayarAjax").click(function () {
    let items = [];

    $("#tablePOS tbody tr").each(function () {
        items.push({
            kode: $(this).find("td:eq(0)").text(),
            jumlah: $(this).find(".jumlah").val(),
            subtotal: $(this).find(".subtotal").text().replace(/\./g, ""),
        });
    });

    $.ajax({
        url: "/transaksi",
        method: "POST",

        data: {
            items: items,
        },

        success: function () {
            Swal.fire({
                icon: "success",
                title: "Transaksi berhasil disimpan",
            });

            $("#tablePOS tbody").html("");

            updateTotal();
            clearInput();
        },

        error: function () {
            Swal.fire({
                icon: "error",
                title: "Terjadi kesalahan",
            });
        },
    });
});
