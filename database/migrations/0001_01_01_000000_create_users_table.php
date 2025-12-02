<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk membuat tabel users
 * yang menyimpan data pengguna aplikasi label generator
 *
 * User menggunakan NP (Nomor Pegawai) sebagai identifier unik
 * untuk login, yaitu: menggantikan email pada sistem standar Laravel
 */
return new class extends Migration
{
    /**
     * Membuat tabel users, password_reset_tokens, dan sessions
     * dengan schema yang disesuaikan untuk kebutuhan production
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('np', 5)->unique()->comment('Nomor Pegawai sebagai login identifier');
            $table->string('name', 100)->nullable()->comment('Nama lengkap pengguna (opsional)');
            $table->string('password');
            $table->enum('role', ['admin', 'operator'])->default('operator')->comment('Role pengguna dalam sistem');
            $table->foreignId('workstation_id')
                ->nullable()
                ->constrained('workstations')
                ->nullOnDelete()
                ->comment('Workstation yang di-assign ke user');
            $table->boolean('is_active')->default(true)->comment('Status aktif user');
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('is_active');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('np', 5)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Menghapus tabel users, password_reset_tokens, dan sessions
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
