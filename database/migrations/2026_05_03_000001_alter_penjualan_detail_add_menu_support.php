<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE penjualan_detail ALTER COLUMN idbarang DROP NOT NULL');
        DB::statement("ALTER TABLE penjualan_detail ADD COLUMN IF NOT EXISTS idmenu integer NULL");
        DB::statement("ALTER TABLE penjualan_detail ADD COLUMN IF NOT EXISTS item_type varchar(10) NOT NULL DEFAULT 'barang'");

        try {
            DB::statement('ALTER TABLE penjualan_detail ADD CONSTRAINT penjualan_detail_idmenu_foreign FOREIGN KEY (idmenu) REFERENCES menu(idmenu) ON DELETE CASCADE');
        } catch (\Throwable $e) {
            // Constraint already exists or table not ready; ignore.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE penjualan_detail DROP CONSTRAINT IF EXISTS penjualan_detail_idmenu_foreign');
        } catch (\Throwable $e) {
            // ignore
        }

        DB::statement('ALTER TABLE penjualan_detail DROP COLUMN IF EXISTS item_type');
        DB::statement('ALTER TABLE penjualan_detail DROP COLUMN IF EXISTS idmenu');
        DB::statement('ALTER TABLE penjualan_detail ALTER COLUMN idbarang SET NOT NULL');
    }
};
