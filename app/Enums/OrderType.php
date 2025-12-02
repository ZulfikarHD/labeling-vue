<?php

namespace App\Enums;

/**
 * Enum untuk tipe order produksi
 * yang menentukan workflow processing label
 *
 * Regular order memiliki 2 label per rim (left + right)
 * sedangkan MMEA order memiliki 1 label per rim tanpa cut side
 */
enum OrderType: string
{
    case Regular = 'regular';
    case Mmea = 'mmea';

    /**
     * Mendapatkan label display untuk UI
     */
    public function label(): string
    {
        return match ($this) {
            self::Regular => 'Regular',
            self::Mmea => 'MMEA',
        };
    }

    /**
     * Mendapatkan deskripsi tipe order
     */
    public function description(): string
    {
        return match ($this) {
            self::Regular => 'Order reguler dengan 2 label per rim (left + right)',
            self::Mmea => 'Order MMEA dengan 1 label per rim tanpa cut side',
        };
    }

    /**
     * Menentukan jumlah label per rim berdasarkan tipe
     */
    public function labelsPerRim(): int
    {
        return match ($this) {
            self::Regular => 2,
            self::Mmea => 1,
        };
    }

    /**
     * Menentukan apakah tipe order memerlukan cut side
     */
    public function requiresCutSide(): bool
    {
        return match ($this) {
            self::Regular => true,
            self::Mmea => false,
        };
    }
}
