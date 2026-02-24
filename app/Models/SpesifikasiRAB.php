<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpesifikasiRAB extends Model
{
    use HasFactory;
    protected $table = 'spec_rab';
    protected $primaryKey = 'id_spec';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_spec',
        'spec_rab',
        'norutspec',
        'kategori',
        'status'
    ];

    /**
     * Boot method untuk auto-generate id_spec dan set default status
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-generate id_spec jika belum ada
            if (empty($model->id_spec)) {
                $model->id_spec = static::generateNextIdSpec();
            }

            // Set default status jika belum ada
            if (empty($model->status)) {
                $model->status = 'A';
            }
        });
    }

    /**
     * Scope untuk pengurutan berdasarkan norutspec kemudian id_spec
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('id_spec', 'asc')
                    ->orderBy('norutspec', 'asc');
    }

    /**
     * Generate ID Spec berikutnya (format: 0001, 0002, dst)
     */
    public static function generateNextIdSpec()
    {
        $lastSpec = self::orderBy('id_spec', 'desc')->first();

        if (!$lastSpec) {
            return '0001';
        }

        $lastNumber = intval($lastSpec->id_spec);
        $nextNumber = $lastNumber + 1;

        return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

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
     * Get kategori label
     */
    public function getKategoriLabelAttribute()
    {
        $labels = [
            'PDP' => 'Pendapatan',
            'HPP' => 'Harga Pokok Penjualan'
        ];

        return $labels[$this->kategori] ?? $this->kategori;
    }

    /**
     * Accessor untuk status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status === 'A' ? 'Aktif' : 'Non Aktif';
    }
}
