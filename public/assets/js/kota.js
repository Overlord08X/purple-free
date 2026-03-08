(function($){
    "use strict";

    $(document).ready(function(){

        // --- Select Biasa ---
        $("#btnTambahKota").click(function(){
            let kota = $("#inputKota").val().trim();
            if(!kota) return;
            $("#selectKota").append(`<option value="${kota}">${kota}</option>`);
            $("#kotaTerpilih").text(kota);
            $("#inputKota").val("");
        });

        $("#selectKota").on('change', function(){
            $("#kotaTerpilih").text($(this).val());
        });

        // --- Select2 ---
        $("#selectKota2").select2({
            placeholder: "Pilih Kota",
            width: "100%",
            tags: true,
            allowClear: true
        });

        $("#btnTambahKota2").click(function(e){
            e.preventDefault();
            let val = $("#inputKota2").val().trim();
            if(!val) return;

            // Cek duplikat
            if($("#selectKota2 option").filter(function(){
                return $(this).val().toLowerCase() === val.toLowerCase();
            }).length){
                alert("Kota sudah ada!");
                return;
            }

            // Tambahkan
            let newOption = new Option(val, val, true, true);
            $("#selectKota2").append(newOption).trigger('change');

            $("#kotaTerpilih2").text(val);
            $("#inputKota2").val("");
        });

        $("#selectKota2").on('change', function(){
            $("#kotaTerpilih2").text($(this).val());
        });

    });

})(jQuery);