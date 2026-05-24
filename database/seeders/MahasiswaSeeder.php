<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use Illuminate\Database\Seeder;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            [
                'nama' => 'Andi Pratama',
                'nim' => '22110001',
                'serial_number_nfc' => '04:A1:B2:C3:D4:E5:61',
            ],
            [
                'nama' => 'Bunga Lestari',
                'nim' => '22110002',
                'serial_number_nfc' => '04:AA:10:22:33:44:55',
            ],
            [
                'nama' => 'Cahyo Ramadhan',
                'nim' => '22110003',
                'serial_number_nfc' => '04:BC:98:76:54:32:10',
            ],
            [
                'nama' => 'Dewi Kartika',
                'nim' => '22110004',
                'serial_number_nfc' => '04:DE:AD:BE:EF:00:99',
            ],
            [
                'nama' => 'Eko Saputra',
                'nim' => '22110005',
                'serial_number_nfc' => '04:F0:0D:CA:FE:12:34',
            ],
        ];

        foreach ($rows as $row) {
            Mahasiswa::updateOrCreate(
                ['nim' => $row['nim']],
                [
                    'nama' => $row['nama'],
                    'serial_number_nfc' => strtoupper($row['serial_number_nfc']),
                ]
            );
        }
    }
}
