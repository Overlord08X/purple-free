<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $primaryKey = 'idpesanan';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'total',
        'metode_bayar',
        'status_bayar',
        'transaction_id',
        'payment_type',
        'order_id',
        'payment_details'
    ];

    protected $casts = [
        'timestamp' => 'datetime'
    ];

    // RELATION
    public function detail()
    {
        return $this->hasMany(DetailPesanan::class, 'idpesanan');
    }

    // ACCESSOR (biar readable)
    public function getStatusTextAttribute()
    {
        return $this->status_bayar == 1 ? 'Lunas' : 'Pending';
    }

    public function getMetodeTextAttribute()
    {
        return match ($this->metode_bayar) {
            1 => 'Virtual Account',
            2 => 'QRIS',
            default => 'Unknown'
        };
    }
}
