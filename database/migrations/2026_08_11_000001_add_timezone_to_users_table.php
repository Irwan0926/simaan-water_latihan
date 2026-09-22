<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Preferensi zona waktu per pengguna.
 *
 * Kolom ini HANYA memengaruhi tampilan dan pengelompokan tanggal pada
 * agregasi. Penyimpanan datetime tetap UTC.
 * NULL = ikuti hasil deteksi browser / config('app.display_timezone').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone', 64)->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
