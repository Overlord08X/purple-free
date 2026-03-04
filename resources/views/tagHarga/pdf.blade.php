<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 8mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 100%;
        }

        .row {
            display: table;
            width: 100%;
        }

        .label {
            display: table-cell;
            width: 20%;
            /* 5 kolom */
            height: 70px;
            /* fix tinggi */
            text-align: center;
            vertical-align: middle;
            box-sizing: border-box;
            padding: 3px;
        }

        .nama {
            font-weight: bold;
            font-size: 10px;
        }

        .harga {
            margin-top: 4px;
            font-size: 10px;
        }
    </style>
</head>

<body>

    <div class="container">

        @for($row=0; $row<8; $row++)
            <div class="row">

            @for($col=1; $col<=5; $col++)
                @php
                $index=($row * 5) + $col;
                @endphp

                <div class="label">
                @if(isset($labels[$index]) && $labels[$index])
                <div class="nama">
                    {{ $labels[$index]->nama_barang }}
                </div>
                <div class="harga">
                    Rp {{ number_format($labels[$index]->harga_barang) }}
                </div>
                @endif
    </div>

    @endfor

    </div>
    @endfor

    </div>

</body>

</html>