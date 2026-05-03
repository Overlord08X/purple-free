$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$("#btnTambah").prop("disabled", true);

let currentItemType = "barang";
let currentItemId = null;
let currentItemCode = null;

function rupiah(n) {
    return new Intl.NumberFormat("id-ID").format(n);
}

function loadItem(kode) {
    return $.get("/kantin/item/" + kode);
}

function fillItem(data) {
    if (!data) {
        Swal.fire({
            icon: "error",
            title: "Item tidak ditemukan",
        });
        return;
    }

    currentItemType = data.type || "barang";
    currentItemId = data.id;
    currentItemCode = data.code || data.id;

    $("#idBarang").val(currentItemCode);
    $("#namaBarang").val(data.name || data.nama_barang || data.nama_menu);
    $("#hargaBarang").val(data.price || data.harga_barang || data.harga);
    $("#jumlah").val(1);

    $("#btnTambah").prop("disabled", false);
}

// PILIH BARANG DARI TABEL
$("#tableBarang").on("click", ".pilih-barang", function () {
    fillItem({
        type: $(this).data("type") || "barang",
        id: $(this).data("id") || $(this).data("kode"),
        code: $(this).data("code") || $(this).data("kode"),
        name: $(this).data("name") || $(this).data("nama"),
        price: $(this).data("price") || $(this).data("harga"),
    });
});

// PILIH MENU DARI TABEL
$("#tableMenu").on("click", ".pilih-menu", function () {
    fillItem({
        type: $(this).data("type") || "menu",
        id: $(this).data("id"),
        code: $(this).data("code"),
        name: $(this).data("name"),
        price: $(this).data("price"),
    });
});

// FILTER MENU BY VENDOR
$("#filterVendorMenu").on("change", function () {
    const vendorId = $(this).val();

    $("#tableMenu tbody tr").each(function () {
        const rowVendor = String($(this).data("vendor"));
        if (vendorId === "all" || vendorId === rowVendor) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// AUTO CARI BARANG/MENU
$("#idBarang").keypress(function (e) {
    if (e.which == 13) {
        let kode = $(this).val();

        loadItem(kode)
            .done(function (data) {
                fillItem(data);
            })
            .fail(function () {
                Swal.fire({
                    icon: "error",
                    title: "Item tidak ditemukan",
                });
            });
    }
});

// TAMBAH BARANG/MENU
$("#btnTambah").click(function () {
    $("#spinnerTambah").show();
    $("#textTambah").text("Menambahkan...");
    $("#btnTambah").prop("disabled", true);

    setTimeout(function () {
        let kode = $("#idBarang").val();
        let nama = $("#namaBarang").val();
        let harga = parseInt($("#hargaBarang").val());
        let jumlah = parseInt($("#jumlah").val());
        let type = currentItemType || "barang";
        let id = currentItemId || kode;

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
            <tr data-type="${type}" data-id="${id}">
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

    currentItemType = "barang";
    currentItemId = null;
    currentItemCode = null;

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
            type: $(this).data("type") || "barang",
            id: $(this).data("id") || $(this).find("td:eq(0)").text(),
            code: $(this).find("td:eq(0)").text(),
            jumlah: $(this).find(".jumlah").val(),
            subtotal: $(this).find(".subtotal").text().replace(/\./g, ""),
        });
    });

    if (items.length == 0) {
        Swal.fire("Tidak ada transaksi");

        $("#spinnerBayarAxios").hide();
        $("#textBayarAxios").html(
            '<i class="mdi mdi-cash"></i> Lanjutkan Pembayaran',
        );
        $("#btnBayar").prop("disabled", false);
        return;
    }

    axios
        .post("/transaksi", {
            items: items,
        })
        .then(function (response) {
            if (response.data && response.data.redirect) {
                window.location.href = response.data.redirect;
            } else {
                Swal.fire({
                    icon: "success",
                    title: "Transaksi berhasil disimpan",
                    text: "Redirect gagal, tidak ada URL payment",
                });
            }
        })
        .catch(function (error) {
            const serverMessage =
                error.response?.data?.error ||
                error.response?.data?.message ||
                "Gagal menyimpan transaksi";

            Swal.fire({
                icon: "error",
                title: "Gagal menyimpan transaksi",
                text: serverMessage,
            });
        })
        .finally(function () {
            $("#spinnerBayarAxios").hide();
            $("#textBayarAxios").html(
                '<i class="mdi mdi-cash"></i> Lanjutkan Pembayaran',
            );
            $("#btnBayar").prop("disabled", false);
        });
});

// BAYAR Ajax
$("#btnBayarAjax").click(function () {
    let items = [];

    $("#tablePOS tbody tr").each(function () {
        items.push({
            type: $(this).data("type") || "barang",
            id: $(this).data("id") || $(this).find("td:eq(0)").text(),
            code: $(this).find("td:eq(0)").text(),
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
