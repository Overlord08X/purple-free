<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendor';
    protected $primaryKey = 'idvendor';

    public $timestamps = false;

    protected $fillable = [
        'nama_vendor',
        'barcode',
        'latitude',
        'longitude',
        'accuracy'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
    ];

    // RELATION
    public function menus()
    {
        return $this->hasMany(Menu::class, 'idvendor');
    }
}
