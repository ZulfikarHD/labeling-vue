<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model ProductionOrder yang merepresentasikan order produksi
 * dalam sistem label generator
 *
 * Production Order berisi informasi order dari SIRINE API, yaitu:
 * PO number, jumlah lembar, rim, dan status processing
 *
 * @property int $id
 * @property int $po_number
 * @property string|null $obc_number
 * @property OrderType $order_type
 * @property string $product_type
 * @property int $total_sheets
 * @property int $total_rims
 * @property int $start_rim
 * @property int $end_rim
 * @property int $inschiet_sheets
 * @property int|null $team_id
 * @property OrderStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Workstation|null $team
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Label> $labels
 * @property-read bool $has_inschiet
 * @property-read int $progress
 */
class ProductionOrder extends Model
{
    /** @use HasFactory<\Database\Factories\ProductionOrderFactory> */
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal
     *
     * @var list<string>
     */
    protected $fillable = [
        'po_number',
        'obc_number',
        'order_type',
        'product_type',
        'total_sheets',
        'total_rims',
        'start_rim',
        'end_rim',
        'inschiet_sheets',
        'team_id',
        'status',
    ];

    /**
     * Mendefinisikan cast untuk atribut model
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_type' => OrderType::class,
            'status' => OrderStatus::class,
            'po_number' => 'integer',
            'total_sheets' => 'integer',
            'total_rims' => 'integer',
            'start_rim' => 'integer',
            'end_rim' => 'integer',
            'inschiet_sheets' => 'integer',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Relasi ke workstation (team) yang di-assign untuk order ini
     *
     * @return BelongsTo<Workstation, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Workstation::class, 'team_id');
    }

    /**
     * Relasi ke labels yang dimiliki order ini
     *
     * @return HasMany<Label, $this>
     */
    public function labels(): HasMany
    {
        return $this->hasMany(Label::class, 'production_order_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope untuk filter order dengan tipe regular
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeRegular(Builder $query): Builder
    {
        return $query->where('order_type', OrderType::Regular);
    }

    /**
     * Scope untuk filter order dengan tipe MMEA
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeMmea(Builder $query): Builder
    {
        return $query->where('order_type', OrderType::Mmea);
    }

    /**
     * Scope untuk filter order dengan status registered
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeRegistered(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Registered);
    }

    /**
     * Scope untuk filter order dengan status in_progress
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::InProgress);
    }

    /**
     * Scope untuk filter order dengan status completed
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Completed);
    }

    /**
     * Scope untuk filter order berdasarkan team
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    // ==================== ACCESSORS ====================

    /**
     * Accessor untuk menentukan apakah order memiliki inschiet
     *
     * @return Attribute<bool, never>
     */
    protected function hasInschiet(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->inschiet_sheets > 0,
        );
    }

    /**
     * Accessor untuk menghitung progress order dalam persen
     * berdasarkan jumlah label yang sudah selesai
     *
     * @return Attribute<int, never>
     */
    protected function progress(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                $totalLabels = $this->labels()->count();
                if ($totalLabels === 0) {
                    return 0;
                }

                $completedLabels = $this->labels()->whereNotNull('finished_at')->count();

                return (int) round(($completedLabels / $totalLabels) * 100);
            },
        );
    }

    // ==================== HELPER METHODS ====================

    /**
     * Menentukan apakah order adalah tipe regular
     */
    public function isRegular(): bool
    {
        return $this->order_type === OrderType::Regular;
    }

    /**
     * Menentukan apakah order adalah tipe MMEA
     */
    public function isMmea(): bool
    {
        return $this->order_type === OrderType::Mmea;
    }

    /**
     * Menentukan apakah order sudah selesai
     */
    public function isCompleted(): bool
    {
        return $this->status === OrderStatus::Completed;
    }
}
