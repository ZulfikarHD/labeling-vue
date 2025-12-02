<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk membuat tabel workstations
 * yang menyimpan data stasiun kerja atau tim produksi
 *
 * Workstation merupakan entitas untuk mengelompokkan operator
 * dalam satu tim kerja, yaitu: assignment production order
 * dan tracking label per tim
 */
return new class extends Migration
{
    /**
     * Membuat tabel workstations dengan struktur
     * untuk menyimpan informasi stasiun kerja
     */
    public function up(): void
    {
        Schema::create('workstations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('Nama workstation, e.g., "Team 1", "WS-05"');
            $table->boolean('is_active')->default(true)->comment('Status aktif workstation');
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel workstations
     */
    public function down(): void
    {
        Schema::dropIfExists('workstations');
    }
};
