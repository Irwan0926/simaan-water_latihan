<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tren_thresholds');
    }

    public function down(): void
    {
        // Tabel tren_thresholds tidak dibuat ulang (fitur ambang batas persen dihapus,
        // tren kini memakai perbandingan sederhana sesuai PLAN.md).
    }
};
