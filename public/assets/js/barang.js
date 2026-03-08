$(document).ready(function () {
    var table = $("#tableBarang").DataTable({
        pageLength: 5,
    });

    // ===== Tambah Barang =====
    $("#btnTambahBarang").click(function () {
        const form = $("#formTambah")[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        let btn = $(this);
        btn.html(
            '<span class="spinner-border spinner-border-sm"></span> Loading...',
        ).prop("disabled", true);

        $.post(
            "/barang",
            {
                _token: $('input[name="_token"]').val(),
                nama_barang: $('input[name="nama_barang"]').val(),
                harga_barang: $('input[name="harga_barang"]').val(),
            },
            function (res) {
                location.reload();
            },
        );
    });

    // ===== Hover highlight =====
    $("#tableBarang tbody")
        .on("mouseenter", "tr", function () {
            $(this).css("background-color", "#eef");
        })
        .on("mouseleave", "tr", function () {
            $(this).css("background-color", "");
        });

    // ===== Edit Barang =====
    $("#tableBarang tbody").on("click", ".btn-ubah", function () {
        let row = $(this).closest("tr");
        let id = row.data("id");
        $.get("/barang/" + id + "/edit", function (data) {
            $("#edit_idbarang").val(data.idbarang);
            $("#edit_nama_barang").val(data.nama_barang);
            $("#edit_harga_barang").val(data.harga_barang);
            $("#modalEditBarang").modal("show");
        });
    });

    // ===== Update Barang dengan Spinner =====
    $("#btnUpdateBarang").click(function () {
        const form = $("#formEditBarang")[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        let btn = $(this);
        btn.html(
            '<span class="spinner-border spinner-border-sm"></span> Loading...',
        ).prop("disabled", true);

        let id = $("#edit_idbarang").val();
        $.ajax({
            url: "/barang/" + id,
            method: "PUT",
            data: {
                _token: $('input[name="_token"]').val(),
                nama_barang: $("#edit_nama_barang").val(),
                harga_barang: $("#edit_harga_barang").val(),
            },
            success: function (res) {
                location.reload();
            },
        });
    });

    // ===== Hapus Barang =====
    $("#tableBarang tbody").on("click", ".btn-hapus", function () {
        if (!confirm("Yakin hapus barang ini?")) return;
        let row = $(this).closest("tr");
        let id = row.data("id");

        $.ajax({
            url: "/barang/" + id,
            method: "DELETE",
            data: { _token: $('input[name="_token"]').val() },
            success: function () {
                location.reload();
            },
        });
    });
});
