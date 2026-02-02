<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecRabDetail extends Model
{
    protected $table = 'spec_rab_detail';
    
    // Composite primary key - tidak ada auto increment
    public $incrementing = false;
    protected $primaryKey = null; // Composite key

    protected $fillable = [
        'id_spec',
        'cost_element',
        'description_ce',
    ];

    /**
     * Override getKey untuk composite key
     */
    public function getKey()
    {
        return [
            'id_spec' => $this->id_spec,
            'cost_element' => $this->cost_element,
        ];
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
     * Scope untuk mencari berdasarkan cost element
     */
    public function scopeByCostElement($query, string $costElement)
    {
        return $query->where('cost_element', $costElement);
    }

    /**
     * Scope untuk mencari berdasarkan id_spec
     */
    public function scopeByIdSpec($query, string $idSpec)
    {
        return $query->where('id_spec', $idSpec);
    }

    /**
     * Static method untuk find by composite key
     */
    public static function findByCompositeKey(string $idSpec, string $costElement)
    {
        return static::where('id_spec', $idSpec)
                     ->where('cost_element', $costElement)
                     ->first();
    }

    /**
     * Static method untuk mendapatkan id_spec dari cost_element
     */
    public static function getIdSpecByCostElement(string $costElement): ?string
    {
        $detail = static::where('cost_element', $costElement)->first();
        return $detail?->id_spec;
    }

    /**
     * Static method untuk mendapatkan kategori dari cost_element
     */
    public static function getKategoriByCostElement(string $costElement): ?string
    {
        $detail = static::with('spesifikasiRab')
                        ->where('cost_element', $costElement)
                        ->first();
        return $detail?->spesifikasiRab?->kategori;
    }
}
