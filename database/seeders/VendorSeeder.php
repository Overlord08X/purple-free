<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vendor;
use App\Models\Menu;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            ['nama_vendor' => 'Kantin A'],
            ['nama_vendor' => 'Kantin B'],
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }

        $menus = [
            ['nama_menu' => 'Nasi Goreng', 'harga' => 15000, 'idvendor' => 1],
            ['nama_menu' => 'Ayam Bakar', 'harga' => 20000, 'idvendor' => 1],
            ['nama_menu' => 'Mie Goreng', 'harga' => 12000, 'idvendor' => 2],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}