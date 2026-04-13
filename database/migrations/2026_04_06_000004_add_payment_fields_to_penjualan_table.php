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
            if (!Schema::hasColumn('penjualan', 'order_id')) {
                $table->string('order_id')->nullable();
            }
            if (!Schema::hasColumn('penjualan', 'transaction_id')) {
                $table->string('transaction_id')->nullable();
            }
            if (!Schema::hasColumn('penjualan', 'payment_type')) {
                $table->string('payment_type')->nullable();
            }
            if (!Schema::hasColumn('penjualan', 'payment_details')) {
                $table->json('payment_details')->nullable();
            }
            if (!Schema::hasColumn('penjualan', 'status_bayar')) {
                $table->tinyInteger('status_bayar')->default(0);
            }
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