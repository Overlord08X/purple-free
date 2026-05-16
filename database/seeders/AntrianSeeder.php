<?php

namespace Database\Seeders;

use App\Models\Antrian;
use Illuminate\Database\Seeder;

class AntrianSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nomor_antrian' => 128, 'nama' => 'Andi Saputra', 'status' => 'menunggu'],
            ['nomor_antrian' => 129, 'nama' => 'Siti Aisyah', 'status' => 'menunggu'],
            ['nomor_antrian' => 130, 'nama' => 'Budi Hartono', 'status' => 'dipanggil', 'dipanggil_pada' => now()->subMinutes(3)],
            ['nomor_antrian' => 131, 'nama' => 'Maya Lestari', 'status' => 'selesai', 'dipanggil_pada' => now()->subMinutes(20), 'selesai_pada' => now()->subMinutes(10)],
            ['nomor_antrian' => 132, 'nama' => 'Rafi Ahmad', 'status' => 'terlambat', 'dipanggil_pada' => now()->subMinutes(35)],
        ];

        foreach ($items as $item) {
            Antrian::updateOrCreate(
                ['nomor_antrian' => $item['nomor_antrian']],
                $item
            );
        }
    }
}