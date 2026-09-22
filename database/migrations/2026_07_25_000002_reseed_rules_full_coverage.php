<?php

use Database\Seeders\RuleSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Ganti knowledge base ke 27 rule lengkap (3×3×3).
 * Menghapus premis tren null ("apa saja") yang sebelumnya di R15–R18.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new RuleSeeder)->run();
    }

    public function down(): void
    {
        // Tidak di-rollback ke 18 rule lama (premis null).
    }
};
