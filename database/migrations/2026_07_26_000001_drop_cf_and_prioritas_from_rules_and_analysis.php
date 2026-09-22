<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus sisa Certainty Factor & prioritas.
 *
 * Sistem memakai Forward Chaining (First Match) murni: aturan dipilih hanya
 * dari kecocokan premis penjualan, stok, dan tren. Nilai CF tidak pernah
 * dibaca mesin inferensi (cf_hasil & cf_detail selalu null), dan prioritas
 * tidak lagi dibutuhkan karena 27 kombinasi premis sudah unik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            if (Schema::hasColumn('rules', 'cf_rule')) {
                $table->dropColumn('cf_rule');
            }

            if (Schema::hasColumn('rules', 'prioritas')) {
                $table->dropColumn('prioritas');
            }
        });

        Schema::table('analysis_results', function (Blueprint $table) {
            if (Schema::hasColumn('analysis_results', 'cf_hasil')) {
                $table->dropColumn('cf_hasil');
            }

            if (Schema::hasColumn('analysis_results', 'cf_detail')) {
                $table->dropColumn('cf_detail');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            if (! Schema::hasColumn('rules', 'cf_rule')) {
                $table->decimal('cf_rule', 4, 2)->default(0.80)->after('rekomendasi');
            }

            if (! Schema::hasColumn('rules', 'prioritas')) {
                $table->unsignedTinyInteger('prioritas')->default(1)->after('rekomendasi');
            }
        });

        Schema::table('analysis_results', function (Blueprint $table) {
            if (! Schema::hasColumn('analysis_results', 'cf_hasil')) {
                $table->decimal('cf_hasil', 5, 4)->nullable()->after('rekomendasi');
            }

            if (! Schema::hasColumn('analysis_results', 'cf_detail')) {
                $table->json('cf_detail')->nullable()->after('rekomendasi');
            }
        });
    }
};
