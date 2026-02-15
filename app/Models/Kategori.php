<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategoris';
    protected $primaryKey = 'idkategori';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_kategori'
    ];

    public function bukus()
    {
        return $this->hasMany(Buku::class, 'idkategori', 'idkategori');
    }
}
