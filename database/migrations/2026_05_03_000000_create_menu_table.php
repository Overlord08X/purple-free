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
        if (!Schema::hasTable('menu')) {
            Schema::create('menu', function (Blueprint $table) {
                $table->id('idmenu');
                $table->string('nama_menu');
                $table->integer('harga');
                $table->string('path_gambar')->nullable();
                $table->unsignedBigInteger('idvendor');

                $table->foreign('idvendor')
                    ->references('idvendor')
                    ->on('vendor')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
