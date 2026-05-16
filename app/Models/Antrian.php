<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antrian extends Model
{
    use HasFactory;

    protected $table = 'antrians';

    protected $fillable = [
        'nomor_antrian',
        'nama',
        'status',
        'dipanggil_pada',
        'selesai_pada',
    ];

    protected $casts = [
        'dipanggil_pada' => 'datetime',
        'selesai_pada' => 'datetime',
    ];

    public function getNomorDisplayAttribute(): string
    {
        return str_pad((string) $this->nomor_antrian, 3, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'dipanggil' => 'Dipanggil',
            'selesai' => 'Selesai',
            'terlambat' => 'Terlambat',
            default => 'Menunggu',
        };
    }
}