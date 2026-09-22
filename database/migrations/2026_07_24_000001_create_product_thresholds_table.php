<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_thresholds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // 'penjualan' = batas kondisi penjualan, 'stok' = batas kondisi stok
            $table->enum('tipe', ['penjualan', 'stok']);

            // 1 = terendah, 2 = sedang, 3 = tertinggi
            $table->unsignedTinyInteger('level');

            // Label tampilan bebas (cth. "Cukup", "Rendah", "Banyak")
            $table->string('label');

            // Batas atas untuk level ini (level 3 = null artinya "> batas level 2")
            $table->unsignedInteger('batas')->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'tipe', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_thresholds');
    }
};
