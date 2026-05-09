<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor', 'barcode')) {
                $table->string('barcode', 255)->nullable()->after('nama_vendor');
            }

            if (!Schema::hasColumn('vendor', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('barcode');
            }

            if (!Schema::hasColumn('vendor', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            if (!Schema::hasColumn('vendor', 'accuracy')) {
                $table->decimal('accuracy', 8, 2)->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor', function (Blueprint $table) {
            if (Schema::hasColumn('vendor', 'accuracy')) {
                $table->dropColumn('accuracy');
            }

            if (Schema::hasColumn('vendor', 'longitude')) {
                $table->dropColumn('longitude');
            }

            if (Schema::hasColumn('vendor', 'latitude')) {
                $table->dropColumn('latitude');
            }

            if (Schema::hasColumn('vendor', 'barcode')) {
                $table->dropColumn('barcode');
            }
        });
    }
};