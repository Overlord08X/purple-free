<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            size: A4 portrait;
            margin: 4mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 20.4cm;   /* 21 - (0.3 × 2) */
            height: 29.1cm;  /* 29.7 - (0.3 × 2) */
        }

        .row {
            width: 100%;
            clear: both;
            margin-bottom: 0.2cm; /* jarak vertikal */
        }

        .row:last-child {
            margin-bottom: 0;
        }

        .label {
            width: 38mm;
            height: 18mm;
            float: left;
            margin-right: 0.3cm; /* jarak horizontal */
            box-sizing: border-box;

            /* CENTER PERFECT */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        /* Kolom ke-5 tidak ada margin kanan */
        .row .label:nth-child(5) {
            margin-right: 0;
        }

        .kode {
            font-size: 7px;
            margin-bottom: 2px;
        }

        .nama {
            font-weight: bold;
            font-size: 9px;
            line-height: 1.1;
        }

        .harga {
            font-size: 9px;
            margin-top: 2px;
        }
    </style>
</head>

<body>

    <div class="container">

        @for($row = 0; $row < 14; $row++)
            <div class="row">
                @for($col = 1; $col <= 5; $col++)

                    @php
                        $index = ($row * 5) + $col;
                    @endphp

                    <div class="label">
                        @if(isset($labels[$index]) && $labels[$index])
                            <div class="kode">
                                {{ $labels[$index]->idbarang }}
                            </div>
                            <div class="nama">
                                {{ $labels[$index]->nama_barang }}
                            </div>
                            <div class="harga">
                                Rp {{ number_format($labels[$index]->harga_barang, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>

                @endfor
            </div>
        @endfor

    </div>

</body>
</html>