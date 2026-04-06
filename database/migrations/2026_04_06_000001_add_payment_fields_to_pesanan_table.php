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
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('transaction_id')->nullable();
            $table->string('payment_type')->nullable(); // VA or QRIS
            $table->string('order_id')->nullable();
            $table->json('payment_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'payment_type', 'order_id', 'payment_details']);
        });
    }
};