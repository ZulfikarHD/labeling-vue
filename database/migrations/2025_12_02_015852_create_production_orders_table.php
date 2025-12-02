<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk membuat tabel production_orders
 * yang menyimpan data production order dari sistem SIRINE
 *
 * Production Order merupakan entitas utama yang berisi informasi
 * order produksi, yaitu: PO number, jumlah lembar, rim, dan status
 * yang terintegrasi dengan SIRINE API untuk validasi
 */
return new class extends Migration
{
    /**
     * Membuat tabel production_orders dengan struktur lengkap
     * untuk menampung data production order regular dan MMEA
     */
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('po_number')->unique()->comment('Nomor Production Order dari SIRINE');
            $table->string('obc_number', 50)->nullable()->comment('Nomor OBC reference');
            $table->enum('order_type', ['regular', 'mmea'])->default('regular')->comment('Tipe order: regular atau MMEA');
            $table->string('product_type', 50)->comment('Jenis produk, e.g., pita cukai, hologram');
            $table->unsignedInteger('total_sheets')->comment('Total lembar (sheets)');
            $table->unsignedInteger('total_rims')->comment('Total rim: floor(sheets/1000)');
            $table->unsignedInteger('start_rim')->default(1)->comment('Nomor rim awal');
            $table->unsignedInteger('end_rim')->comment('Nomor rim akhir');
            $table->unsignedInteger('inschiet_sheets')->default(0)->comment('Lembar sisa (remainder)');
            $table->foreignId('team_id')
                ->nullable()
                ->constrained('workstations')
                ->nullOnDelete()
                ->comment('Tim yang ditugaskan untuk order ini');
            $table->enum('status', ['registered', 'in_progress', 'completed'])->default('registered')->comment('Status order');
            $table->timestamps();

            // Indexes untuk query yang sering digunakan
            $table->index('po_number');
            $table->index('status');
            $table->index('order_type');
            $table->index('team_id');
        });
    }

    /**
     * Menghapus tabel production_orders
     */
    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
