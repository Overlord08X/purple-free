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
        Schema::table('penjualan', function (Blueprint $table) {
            $table->string('order_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->json('payment_details')->nullable();
            $table->tinyInteger('status_bayar')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'transaction_id', 'payment_type', 'payment_details', 'status_bayar']);
        });
    }
};