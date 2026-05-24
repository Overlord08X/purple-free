<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'mahasiswa_id',
        'waktu_hadir',
        'attendance_date',
        'status',
    ];

    protected $casts = [
        'waktu_hadir' => 'datetime',
        'attendance_date' => 'date',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
