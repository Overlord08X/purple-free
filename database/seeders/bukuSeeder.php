<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class bukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bukus')->insert([
            [
                'idkategori' => 1,
                'kode' => 'NV-01',
                'judul' => 'Home Sweet Loan',
                'pengarang' => 'Almira Bastari',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'idkategori' => 2,
                'kode' => 'BO-01',
                'judul' => 'Mohammad Hatta, Untuk Negeriku',
                'pengarang' => 'Taufik Abdullah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'idkategori' => 1,
                'kode' => 'NV-02',
                'judul' => 'Keajaiban Toko Kelontong Namiya',
                'pengarang' => 'Keigo Higashino',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
