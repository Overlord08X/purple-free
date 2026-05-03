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
            background: #fff;
            padding: 1mm 1.5mm;

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

        .barcode {
            width: 32mm;
            height: auto;
            margin-bottom: 1mm;
        }

        .kode {
            font-size: 6px;
            margin-bottom: 1px;
        }

        .nama {
            font-weight: bold;
            font-size: 8px;
            line-height: 1.1;
        }

        .harga {
            font-size: 8px;
            margin-top: 1px;
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
                            @php
                                $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                                // Increase module width/height so bars are scanner-friendly on printed labels.
                                $barcode = base64_encode($generator->getBarcode($labels[$index]->code, $generator::TYPE_CODE_128, 2, 42));
                            @endphp
                            <img src="data:image/png;base64,{{ $barcode }}" class="barcode" />
                            <div class="kode">
                                {{ $labels[$index]->code }}
                            </div>
                            <div class="nama">
                                {{ $labels[$index]->name }}
                            </div>
                            <div class="harga">
                                Rp {{ number_format($labels[$index]->price, 0, ',', '.') }}
                            </div>
                            @if(!empty($labels[$index]->vendor_name))
                                <div class="kode" style="font-size:5px; margin-top:1px;">
                                    {{ $labels[$index]->vendor_name }}
                                </div>
                            @endif
                        @endif
                    </div>

                @endfor
            </div>
        @endfor

    </div>

</body>
</html>