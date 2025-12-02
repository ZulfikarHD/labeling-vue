<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Workstation yang merepresentasikan stasiun kerja atau tim produksi
 * dalam sistem label generator
 *
 * Workstation digunakan untuk mengelompokkan operator dan production order
 * dalam satu unit kerja, yaitu: memudahkan assignment dan tracking
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionOrder> $productionOrders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Label> $labels
 */
class Workstation extends Model
{
    /** @use HasFactory<\Database\Factories\WorkstationFactory> */
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_active',
    ];

    /**
     * Mendefinisikan cast untuk atribut model
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Relasi ke users yang terdaftar di workstation ini
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'workstation_id');
    }

    /**
     * Relasi ke production orders yang di-assign ke workstation ini
     *
     * @return HasMany<ProductionOrder, $this>
     */
    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class, 'team_id');
    }

    /**
     * Relasi ke labels yang diproses di workstation ini
     *
     * @return HasMany<Label, $this>
     */
    public function labels(): HasMany
    {
        return $this->hasMany(Label::class, 'workstation_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope untuk filter workstation yang aktif
     *
     * @param  Builder<Workstation>  $query
     * @return Builder<Workstation>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
