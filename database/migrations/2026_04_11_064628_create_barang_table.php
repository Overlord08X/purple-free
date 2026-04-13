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
        if (!Schema::hasTable('barang')) {
            Schema::create('barang', function (Blueprint $table) {
                $table->bigIncrements('idbarang'); // PRIMARY KEY custom
                $table->string('nama_barang');
                $table->decimal('harga_barang', 15, 2);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
