<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    protected $table = 'bukus';
    protected $primaryKey = 'idbuku';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'idkategori',
        'kode',
        'judul',
        'pengarang'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'idkategori', 'idkategori');
    }
}
