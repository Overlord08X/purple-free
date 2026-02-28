<!DOCTYPE html>
<html>
<head>
<style>
.label {
    width: 20%;
    height: 12.5%;
    float: left;
    text-align: center;
    font-size: 10px;
    box-sizing: border-box;
}
</style>
</head>
<body>

@for($i=1; $i<=40; $i++)
<div class="label">
    @if($labels[$i])
        <strong>{{ $labels[$i]->nama_barang }}</strong><br>
        Rp {{ number_format($labels[$i]->harga_barang) }}
    @endif
</div>
@endfor

</body>
</html>