<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk membuat tabel labels
 * yang menyimpan data label per rim dari production order
 *
 * Label merupakan unit terkecil yang di-track dalam sistem,
 * yaitu: satu rim memiliki satu atau dua label (left/right)
 * tergantung pada tipe order (regular atau MMEA)
 */
return new class extends Migration
{
    /**
     * Membuat tabel labels dengan struktur untuk tracking
     * proses inspeksi dan pencetakan label
     */
    public function up(): void
    {
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')
                ->constrained('production_orders')
                ->cascadeOnDelete()
                ->comment('Production order yang memiliki label ini');
            $table->unsignedInteger('rim_number')->comment('Nomor rim: 1, 2, 3... atau 999 untuk inschiet');
            $table->enum('cut_side', ['left', 'right'])->nullable()->comment('Sisi potong: left/right (NULL untuk MMEA)');
            $table->boolean('is_inschiet')->default(false)->comment('TRUE jika rim 999 (inschiet)');
            $table->string('inspector_np', 5)->nullable()->comment('NP pemeriksa utama');
            $table->string('inspector_2_np', 5)->nullable()->comment('NP pemeriksa kedua (opsional)');
            $table->unsignedInteger('pack_sheets')->nullable()->comment('Lembar per kemasan (khusus MMEA)');
            $table->dateTime('started_at')->nullable()->comment('Waktu mulai inspeksi');
            $table->dateTime('finished_at')->nullable()->comment('Waktu selesai inspeksi');
            $table->foreignId('workstation_id')
                ->nullable()
                ->constrained('workstations')
                ->nullOnDelete()
                ->comment('Workstation tempat label diproses');
            $table->timestamps();

            // Composite unique index untuk mencegah duplikasi label
            $table->unique(['production_order_id', 'rim_number', 'cut_side'], 'labels_order_rim_side_unique');

            // Indexes untuk query yang sering digunakan
            $table->index('production_order_id');
            $table->index('inspector_np');
        });
    }

    /**
     * Menghapus tabel labels
     */
    public function down(): void
    {
        Schema::dropIfExists('labels');
    }
};
