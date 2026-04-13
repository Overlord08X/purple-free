<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    protected $table = 'penjualan_detail';
    protected $primaryKey = 'idpenjualan_detail'; // sesuaikan kalau beda

    protected $fillable = [
        'idpenjualan',
        'idbarang',
        'jumlah',
        'harga',
        'subtotal'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'idpenjualan', 'idpenjualan');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'idbarang', 'idbarang');
    }
}