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
        Schema::create('analysis_results', function (Blueprint $table) {

    $table->id();

    $table->foreignId('product_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->string('periode');

    $table->string('kondisi_penjualan');

    $table->string('kondisi_stok');

    $table->string('tren');

    $table->string('rule_terpakai');

    $table->string('rekomendasi');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
};
