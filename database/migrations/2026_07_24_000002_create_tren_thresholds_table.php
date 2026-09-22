<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tren_thresholds', function (Blueprint $table) {
            $table->id();

            // 'naik', 'stabil', 'turun'
            $table->string('key')->unique();

            // Batas bawah persentase (null = tidak ada batas bawah)
            $table->decimal('min', 6, 2)->nullable();

            // Batas atas persentase (null = tidak ada batas atas)
            $table->decimal('max', 6, 2)->nullable();

            // Label tampilan (cth. "> +10%", "-10% s/d +10%")
            $table->string('label');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tren_thresholds');
    }
};
