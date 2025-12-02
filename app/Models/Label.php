<?php

namespace App\Models;

use App\Enums\CutSide;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Label yang merepresentasikan label per rim
 * dalam sistem label generator
 *
 * Label merupakan unit terkecil yang di-track, yaitu:
 * satu rim memiliki satu atau dua label tergantung tipe order
 *
 * @property int $id
 * @property int $production_order_id
 * @property int $rim_number
 * @property CutSide|null $cut_side
 * @property bool $is_inschiet
 * @property string|null $inspector_np
 * @property string|null $inspector_2_np
 * @property int|null $pack_sheets
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property int|null $workstation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProductionOrder $order
 * @property-read \App\Models\Workstation|null $workstation
 * @property-read bool $is_completed
 * @property-read bool $is_in_progress
 */
class Label extends Model
{
    /** @use HasFactory<\Database\Factories\LabelFactory> */
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal
     *
     * @var list<string>
     */
    protected $fillable = [
        'production_order_id',
        'rim_number',
        'cut_side',
        'is_inschiet',
        'inspector_np',
        'inspector_2_np',
        'pack_sheets',
        'started_at',
        'finished_at',
        'workstation_id',
    ];

    /**
     * Mendefinisikan cast untuk atribut model
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cut_side' => CutSide::class,
            'is_inschiet' => 'boolean',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'rim_number' => 'integer',
            'pack_sheets' => 'integer',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Relasi ke production order yang memiliki label ini
     *
     * @return BelongsTo<ProductionOrder, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    /**
     * Relasi ke workstation tempat label diproses
     *
     * @return BelongsTo<Workstation, $this>
     */
    public function workstation(): BelongsTo
    {
        return $this->belongsTo(Workstation::class, 'workstation_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope untuk filter label yang belum diproses (pending)
     *
     * @param  Builder<Label>  $query
     * @return Builder<Label>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('inspector_np');
    }

    /**
     * Scope untuk filter label yang sudah diproses
     *
     * @param  Builder<Label>  $query
     * @return Builder<Label>
     */
    public function scopeProcessed(Builder $query): Builder
    {
        return $query->whereNotNull('inspector_np');
    }

    /**
     * Scope untuk filter label inschiet
     *
     * @param  Builder<Label>  $query
     * @return Builder<Label>
     */
    public function scopeInschiet(Builder $query): Builder
    {
        return $query->where('is_inschiet', true);
    }

    /**
     * Scope untuk filter label berdasarkan production order
     *
     * @param  Builder<Label>  $query
     * @return Builder<Label>
     */
    public function scopeForOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('production_order_id', $orderId);
    }

    // ==================== ACCESSORS ====================

    /**
     * Accessor untuk menentukan apakah label sudah selesai
     *
     * @return Attribute<bool, never>
     */
    protected function isCompleted(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->finished_at !== null,
        );
    }

    /**
     * Accessor untuk menentukan apakah label sedang dalam proses
     *
     * @return Attribute<bool, never>
     */
    protected function isInProgress(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->started_at !== null && $this->finished_at === null,
        );
    }

    // ==================== HELPER METHODS ====================

    /**
     * Memulai proses inspeksi label
     *
     * @param  string  $inspectorNp  NP pemeriksa
     */
    public function startInspection(string $inspectorNp): void
    {
        $this->update([
            'inspector_np' => $inspectorNp,
            'started_at' => now(),
        ]);
    }

    /**
     * Menyelesaikan proses inspeksi label
     *
     * @param  string|null  $inspector2Np  NP pemeriksa kedua (opsional)
     */
    public function finishInspection(?string $inspector2Np = null): void
    {
        $data = ['finished_at' => now()];

        if ($inspector2Np !== null) {
            $data['inspector_2_np'] = $inspector2Np;
        }

        $this->update($data);
    }
}
