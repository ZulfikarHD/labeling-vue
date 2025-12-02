<?php

namespace App\Enums;

/**
 * Enum untuk role pengguna dalam sistem
 * yang menentukan akses dan permission
 *
 * Admin memiliki akses penuh termasuk user management
 * sedangkan Operator hanya dapat memproses label
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Operator = 'operator';

    /**
     * Mendapatkan label display untuk UI
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Operator => 'Operator',
        };
    }

    /**
     * Mendapatkan deskripsi role
     */
    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Akses penuh termasuk user management dan konfigurasi sistem',
            self::Operator => 'Akses untuk memproses label dan cetak label',
        };
    }

    /**
     * Menentukan apakah role memiliki akses admin
     */
    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Mendapatkan warna badge untuk tampilan UI
     */
    public function color(): string
    {
        return match ($this) {
            self::Admin => 'purple',
            self::Operator => 'blue',
        };
    }
}
