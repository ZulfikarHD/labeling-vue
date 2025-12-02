<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Workstation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder utama untuk menginisialisasi data awal aplikasi
 * yang mencakup workstations dan user admin default
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database dengan data awal
     * yang diperlukan untuk menjalankan aplikasi
     */
    public function run(): void
    {
        // Buat workstations default
        $workstation1 = Workstation::create(['name' => 'Team 1', 'is_active' => true]);
        Workstation::create(['name' => 'Team 2', 'is_active' => true]);
        Workstation::create(['name' => 'Team 3', 'is_active' => true]);

        // Buat admin default
        User::create([
            'np' => 'ADMIN',
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'workstation_id' => $workstation1->id,
            'is_active' => true,
        ]);

        // Buat operator default untuk testing
        User::create([
            'np' => 'OP001',
            'name' => 'Operator 1',
            'password' => Hash::make('password'),
            'role' => UserRole::Operator,
            'workstation_id' => $workstation1->id,
            'is_active' => true,
        ]);
    }
}
