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
        Schema::create('rules', function (Blueprint $table) {

    $table->id();

    $table->string('kode_rule');

    $table->string('penjualan')
          ->nullable();

    $table->string('stok')
          ->nullable();

    $table->string('tren')
          ->nullable();

    $table->string('rekomendasi');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
