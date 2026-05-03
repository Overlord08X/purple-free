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
        if (!Schema::hasTable('penjualan')) {
            Schema::create('penjualan', function (Blueprint $table) {
                $table->increments('idpenjualan');
                $table->timestamp('created_at');
                $table->integer('total');
                $table->string('order_id')->nullable();
                $table->string('transaction_id')->nullable();
                $table->string('payment_type')->nullable();
                $table->json('payment_details')->nullable();
                $table->smallInteger('status_bayar')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
