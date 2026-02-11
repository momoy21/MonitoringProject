<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecRabDetail extends Model
{
    protected $table = 'spec_rab_detail';
    
    // Primary key is cost_element (unique)
    protected $primaryKey = 'cost_element';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_spec',
        'cost_element',
        'description_ce',
        'status',
    ];

    /**
     * Scope untuk hanya data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    /**
     * Scope untuk hanya data non-aktif
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'N');
    }

    /**
     * Accessor untuk status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status === 'A' ? 'Aktif' : 'Non Aktif';
    }

    /**
     * Relasi ke SpesifikasiRAB (Config Spec RAB)
     */
    public function spesifikasiRab(): BelongsTo
    {
        return $this->belongsTo(SpesifikasiRAB::class, 'id_spec', 'id_spec');
    }

    /**
     * Get kategori dari parent SpesifikasiRAB
     */
    public function getKategoriAttribute(): ?string
    {
        return $this->spesifikasiRab?->kategori;
    }

    /**
     * Static method untuk mendapatkan id_spec dari cost_element
     */
    public static function getIdSpecByCostElement(string $costElement): ?string
    {
        // Karena cost_element sekarang PK, kita bisa langsung find
        $detail = static::find($costElement);
        return $detail?->id_spec;
    }

    /**
     * Static method untuk mendapatkan kategori dari cost_element
     */
    public static function getKategoriByCostElement(string $costElement): ?string
    {
        $detail = static::with('spesifikasiRab')->find($costElement);
        return $detail?->spesifikasiRab?->kategori;
    }
}
