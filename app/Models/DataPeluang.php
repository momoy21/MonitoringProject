<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DataPeluang extends Model
{
    use HasFactory;

    protected $table = 'data_peluang';
    protected $primaryKey = 'id_datapeluang';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_datapeluang',
        'peluang',
        'id_konsumen',
        'kontak_person',
        'no_hp',
        'lokasi',
        'tgl_peluang',
        'target_peluang',
        'biaya_peluang',
        'pagu_peluang',
        'status'
    ];

    protected $casts = [
        'tgl_peluang' => 'date',
        'target_peluang' => 'date',
        'biaya_peluang' => 'decimal:2',
        'pagu_peluang' => 'decimal:2'
    ];

    protected $attributes = [
        'status' => 'N'  // Default status to New
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_datapeluang)) {
                $model->id_datapeluang = static::generateIdDataPeluang();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'id_datapeluang';
    }

    public static function generateIdDataPeluang()
    {
        $lastDataPeluang = static::orderByRaw('CAST(id_datapeluang AS UNSIGNED) DESC')->first();

        if (!$lastDataPeluang) {
            return '0001';
        }

        $lastNumber = (int) $lastDataPeluang->id_datapeluang;
        $newNumber = $lastNumber + 1;

        return str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'id_konsumen', 'id_konsumen');
    }

    public function scopeSearch(Builder $query, $search)
    {
        if (!empty($search)) {
            return $query->where('peluang', 'LIKE', "%{$search}%")
                        // ->orWhere('id_datapeluang', 'LIKE', "%{$search}%")
                        ->orWhereHas('konsumen', function ($q) use ($search) {
                            $q->where('konsumen', 'LIKE', "%{$search}%");
                        });
        }

        return $query;
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'N' => 'New',
            'I' => 'In Progress',
            'D' => 'Close',
            'C' => 'Cancel',
            default => 'Unknown'
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'N' => 'badge bg-info',
            'I' => 'badge bg-primary',
            'D' => 'badge bg-success',
            'C' => 'badge bg-danger',
            default => 'badge bg-secondary'
        };
    }

    public function getBiayaPeluangFormattedAttribute()
    {
        if (!$this->biaya_peluang || $this->biaya_peluang == 0) {
            return '<div class="text-center text-muted">-</div>';
        }
        $formattedNumber = number_format((float)$this->biaya_peluang, 0, ',', '.');
        return '<div class="d-flex justify-content-between align-items-center" style="gap: 0.5rem;"><span>Rp</span><span>' . $formattedNumber . '</span></div>';
    }

    public function getPaguPeluangFormattedAttribute()
    {
        if (!$this->pagu_peluang || $this->pagu_peluang == 0) {
            return '<div class="text-center text-muted">-</div>';
        }
        $formattedNumber = number_format((float)$this->pagu_peluang, 0, ',', '.');
        return '<div class="d-flex justify-content-between align-items-center" style="gap: 0.5rem;"><span>Rp</span><span>' . $formattedNumber . '</span></div>';
    }
}
