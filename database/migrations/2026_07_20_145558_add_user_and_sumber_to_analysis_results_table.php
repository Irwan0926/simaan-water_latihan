<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_results', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->string('sumber')->default('otomatis')->after('periode');
            $table->unsignedInteger('qty_saat_ini')->nullable()->after('tren');
            $table->unsignedInteger('qty_sebelumnya')->nullable()->after('qty_saat_ini');
            $table->unsignedInteger('stok_saat_analisis')->nullable()->after('qty_sebelumnya');
        });
    }

    public function down(): void
    {
        Schema::table('analysis_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'sumber',
                'qty_saat_ini',
                'qty_sebelumnya',
                'stok_saat_analisis',
            ]);
        });
    }
};
