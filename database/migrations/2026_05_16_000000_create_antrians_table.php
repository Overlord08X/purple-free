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
        Schema::create('antrians', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nomor_antrian')->unique();
            $table->string('nama');
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'terlambat'])->default('menunggu');
            $table->timestamp('dipanggil_pada')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrians');
    }
};