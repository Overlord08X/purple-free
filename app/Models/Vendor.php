<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendor';
    protected $primaryKey = 'idvendor';

    public $timestamps = false;

    protected $fillable = [
        'nama_vendor'
    ];

    // RELATION
    public function menus()
    {
        return $this->hasMany(Menu::class, 'idvendor');
    }
}
