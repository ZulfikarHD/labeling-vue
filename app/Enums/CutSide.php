<?php

namespace App\Enums;

/**
 * Enum untuk sisi potong label pada order regular
 * yang menentukan posisi label dalam satu rim
 *
 * Satu rim regular memiliki 2 label: left dan right
 * dengan processing priority: left sebelum right
 */
enum CutSide: string
{
    case Left = 'left';
    case Right = 'right';

    /**
     * Mendapatkan label display untuk UI
     */
    public function label(): string
    {
        return match ($this) {
            self::Left => 'Kiri',
            self::Right => 'Kanan',
        };
    }

    /**
     * Mendapatkan singkatan untuk tampilan compact
     */
    public function short(): string
    {
        return match ($this) {
            self::Left => 'L',
            self::Right => 'R',
        };
    }

    /**
     * Mendapatkan priority order untuk processing
     * dimana left diproses terlebih dahulu
     */
    public function priority(): int
    {
        return match ($this) {
            self::Left => 1,
            self::Right => 2,
        };
    }

    /**
     * Mendapatkan sisi sebaliknya
     */
    public function opposite(): self
    {
        return match ($this) {
            self::Left => self::Right,
            self::Right => self::Left,
        };
    }
}
