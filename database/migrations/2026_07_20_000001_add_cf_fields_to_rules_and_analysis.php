<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            $table->decimal('cf_rule', 4, 2)->default(0.80)->after('rekomendasi');
            $table->unsignedTinyInteger('prioritas')->default(1)->after('cf_rule');
            $table->string('kategori')->nullable()->after('prioritas');
            $table->text('keterangan')->nullable()->after('kategori');
            $table->boolean('is_active')->default(true)->after('keterangan');
        });

        Schema::table('analysis_results', function (Blueprint $table) {
            $table->decimal('cf_hasil', 5, 4)->nullable()->after('rekomendasi');
            $table->json('cf_detail')->nullable()->after('cf_hasil');
        });
    }

    public function down(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            $table->dropColumn(['cf_rule', 'prioritas', 'kategori', 'keterangan', 'is_active']);
        });

        Schema::table('analysis_results', function (Blueprint $table) {
            $table->dropColumn(['cf_hasil', 'cf_detail']);
        });
    }
};
