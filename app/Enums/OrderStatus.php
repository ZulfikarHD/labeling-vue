<?php

namespace App\Enums;

/**
 * Enum untuk status production order
 * yang menentukan tahapan proses order dalam workflow
 *
 * Status progression: registered -> in_progress -> completed
 */
enum OrderStatus: string
{
    case Registered = 'registered';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    /**
     * Mendapatkan label display untuk UI
     */
    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Terdaftar',
            self::InProgress => 'Dalam Proses',
            self::Completed => 'Selesai',
        };
    }

    /**
     * Mendapatkan warna badge untuk tampilan UI
     */
    public function color(): string
    {
        return match ($this) {
            self::Registered => 'gray',
            self::InProgress => 'blue',
            self::Completed => 'green',
        };
    }

    /**
     * Menentukan apakah order masih dapat diproses
     */
    public function isProcessable(): bool
    {
        return match ($this) {
            self::Registered, self::InProgress => true,
            self::Completed => false,
        };
    }

    /**
     * Mendapatkan status berikutnya dalam workflow
     */
    public function nextStatus(): ?self
    {
        return match ($this) {
            self::Registered => self::InProgress,
            self::InProgress => self::Completed,
            self::Completed => null,
        };
    }
}
