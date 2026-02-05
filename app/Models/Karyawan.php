<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';
    protected $primaryKey = 'nik';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nik',
        'nama',
        'status',
        'aktif',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status Karyawan options
     */
    public const STATUS_OPTIONS = [
        'T' => 'Karyawan Tetap',
        'K' => 'Karyawan Kontrak',
        'J' => 'Karyawan JO',
    ];

    /**
     * Aktif options
     */
    public const AKTIF_OPTIONS = [
        'Y' => 'Ya',
        'T' => 'Tidak',
    ];

    /**
     * Scope untuk hanya data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('aktif', 'Y');
    }

    /**
     * Scope untuk pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nik', 'like', "%{$search}%")
                     ->orWhere('nama', 'like', "%{$search}%");
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'nik';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_OPTIONS[$this->status] ?? $this->status;
    }

    /**
     * Get aktif label
     */
    public function getAktifLabelAttribute(): string
    {
        return self::AKTIF_OPTIONS[$this->aktif] ?? $this->aktif;
    }

    /**
     * Check if karyawan is active
     */
    public function isActive(): bool
    {
        return $this->aktif === 'Y';
    }
}
