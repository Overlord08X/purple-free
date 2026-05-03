<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add foreign keys untuk penjualan_detail
        if (Schema::hasTable('penjualan_detail')) {
            Schema::table('penjualan_detail', function (Blueprint $table) {
                // Check if foreign key doesn't already exist
                if (!Schema::hasColumn('penjualan_detail', 'idpenjualan_fk')) {
                    try {
                        $table->foreign('idpenjualan')
                            ->references('idpenjualan')
                            ->on('penjualan')
                            ->onDelete('cascade');
                    } catch (\Exception $e) {
                        // Foreign key already exists
                    }
                }
                
                try {
                    $table->foreign('idbarang')
                        ->references('idbarang')
                        ->on('barang')
                        ->onDelete('cascade');
                } catch (\Exception $e) {
                    // Foreign key already exists
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('penjualan_detail')) {
            Schema::table('penjualan_detail', function (Blueprint $table) {
                $table->dropForeignKey(['idpenjualan']);
                $table->dropForeignKey(['idbarang']);
            });
        }
    }
};
