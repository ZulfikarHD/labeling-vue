<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model User yang merepresentasikan pengguna aplikasi label generator
 * dengan autentikasi menggunakan NP (Nomor Pegawai)
 *
 * User memiliki role admin atau operator yang menentukan akses
 * dan dapat di-assign ke workstation tertentu
 *
 * @property int $id
 * @property string $np
 * @property string|null $name
 * @property string $password
 * @property UserRole $role
 * @property int|null $workstation_id
 * @property bool $is_active
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Workstation|null $workstation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Label> $inspectedLabels
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal
     *
     * @var list<string>
     */
    protected $fillable = [
        'np',
        'name',
        'password',
        'role',
        'workstation_id',
        'is_active',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mendefinisikan cast untuk atribut model
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Relasi ke workstation tempat user di-assign
     *
     * @return BelongsTo<Workstation, $this>
     */
    public function workstation(): BelongsTo
    {
        return $this->belongsTo(Workstation::class, 'workstation_id');
    }

    /**
     * Relasi ke labels yang diperiksa oleh user ini sebagai inspector utama
     *
     * @return HasMany<Label, $this>
     */
    public function inspectedLabels(): HasMany
    {
        return $this->hasMany(Label::class, 'inspector_np', 'np');
    }

    // ==================== SCOPES ====================

    /**
     * Scope untuk filter user yang aktif
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter user dengan role admin
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', UserRole::Admin);
    }

    /**
     * Scope untuk filter user dengan role operator
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeOperators(Builder $query): Builder
    {
        return $query->where('role', UserRole::Operator);
    }

    // ==================== ACCESSORS ====================

    /**
     * Menentukan apakah user adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Menentukan apakah user adalah operator
     */
    public function isOperator(): bool
    {
        return $this->role === UserRole::Operator;
    }
}
