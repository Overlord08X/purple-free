<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Buat sequence untuk penjualan
        DB::statement('CREATE SEQUENCE IF NOT EXISTS penjualan_seq START 1');

        // Buat tabel penjualan
        if (!Schema::hasTable('penjualan')) {
            Schema::create('penjualan', function (Blueprint $table) {
                $table->integer('idpenjualan')->primary();
                $table->timestamp('created_at');
                $table->integer('total');
                $table->string('order_id')->nullable();
                $table->string('transaction_id')->nullable();
                $table->string('payment_type')->nullable();
                $table->json('payment_details')->nullable();
                $table->smallInteger('status_bayar')->default(0);
            });
        }

        // Buat sequence untuk penjualan_detail
        DB::statement('CREATE SEQUENCE IF NOT EXISTS penjualan_detail_seq START 1');

        // Buat tabel penjualan_detail
        if (!Schema::hasTable('penjualan_detail')) {
            Schema::create('penjualan_detail', function (Blueprint $table) {
                $table->integer('idpenjualan_detail')->primary();
                $table->integer('idpenjualan');
                $table->string('idbarang', 8);
                $table->integer('jumlah');
                $table->integer('subtotal');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_detail');
        Schema::dropIfExists('penjualan');
        DB::statement('DROP SEQUENCE IF EXISTS penjualan_detail_seq');
        DB::statement('DROP SEQUENCE IF EXISTS penjualan_seq');
    }
};
