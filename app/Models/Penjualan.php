<?php

namespace App\Models;

use App\Models\PenjualanDetail;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';
    protected $primaryKey = 'idpenjualan';

    protected $fillable = [
        'total',
        'status_bayar',
        'order_id',
        'transaction_id',
        'payment_type',
        'payment_details'
    ];

    public function detail()
    {
        return $this->hasMany(PenjualanDetail::class, 'idpenjualan', 'idpenjualan');
    }
}