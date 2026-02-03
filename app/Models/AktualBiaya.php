<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AktualBiaya extends Model
{
    protected $table = 'aktual_biaya';
    
    // Composite primary key - tidak ada auto increment
    public $incrementing = false;
    protected $primaryKey = null; // Composite key

    protected $fillable = [
        'cc_projek',
        'id_aktual',
        'id_spec',
        'tanggal_posting',
        'bulan',
        'nilai',
        'kategori',
        'plsap_id',
    ];

    protected $casts = [
        'tanggal_posting' => 'date',
        'bulan' => 'date',
        'nilai' => 'decimal:2',
    ];

    /**
     * Override getKey untuk composite key
     */
    public function getKey()
    {
        return [
            'cc_projek' => $this->cc_projek,
            'id_aktual' => $this->id_aktual,
            'id_spec' => $this->id_spec,
        ];
    }

    /**
     * Relasi ke SpesifikasiRAB
     */
    public function spesifikasiRab(): BelongsTo
    {
        return $this->belongsTo(SpesifikasiRAB::class, 'id_spec', 'id_spec');
    }

    /**
     * Relasi ke Plsap (record SAP asli)
     */
    public function plsap(): BelongsTo
    {
        return $this->belongsTo(Plsap::class, 'plsap_id', 'id');
    }

    /**
     * Scope berdasarkan proyek
     */
    public function scopeByProject($query, string $ccProjek)
    {
        return $query->where('cc_projek', $ccProjek);
    }

    /**
     * Scope berdasarkan bulan
     */
    public function scopeByBulan($query, $bulan)
    {
        if ($bulan instanceof Carbon) {
            $bulan = $bulan->startOfMonth()->format('Y-m-d');
        }
        return $query->where('bulan', $bulan);
    }

    /**
     * Scope berdasarkan periode (range bulan)
     */
    public function scopeByPeriode($query, $startBulan, $endBulan)
    {
        return $query->whereBetween('bulan', [$startBulan, $endBulan]);
    }

    /**
     * Scope berdasarkan kategori
     */
    public function scopeByKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope untuk pendapatan saja
     */
    public function scopePendapatan($query)
    {
        return $query->where('kategori', 'PDP');
    }

    /**
     * Scope untuk HPP saja
     */
    public function scopeHpp($query)
    {
        return $query->where('kategori', 'HPP');
    }

    /**
     * Generate ID Aktual
     * Format: YYYYDDNNNN (4 digit tahun + 2 digit tanggal + 4 digit nomor urut)
     */
    public static function generateIdAktual(): string
    {
        $today = Carbon::now();
        $prefix = $today->format('Y') . $today->format('d'); // e.g., 202631

        // Cari nomor urut terakhir untuk hari ini
        $lastRecord = static::where('id_aktual', 'like', $prefix . '%')
                           ->orderBy('id_aktual', 'desc')
                           ->first();

        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->id_aktual, 6, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Static method untuk find by composite key
     */
    public static function findByCompositeKey(string $ccProjek, string $idAktual, string $idSpec)
    {
        return static::where('cc_projek', $ccProjek)
                     ->where('id_aktual', $idAktual)
                     ->where('id_spec', $idSpec)
                     ->first();
    }

    /**
     * Get formatted bulan (mmm/yyyy)
     */
    public function getBulanFormattedAttribute(): string
    {
        return $this->bulan ? Carbon::parse($this->bulan)->format('M/Y') : '';
    }

    /**
     * Get formatted nilai (dengan separator ribuan)
     */
    public function getNilaiFormattedAttribute(): string
    {
        return number_format($this->nilai ?? 0, 2, ',', '.');
    }

    /**
     * Get total nilai per proyek per bulan
     */
    public static function getTotalByProjectBulan(string $ccProjek, $bulan): float
    {
        return static::byProject($ccProjek)
                     ->byBulan($bulan)
                     ->sum('nilai') ?? 0;
    }

    /**
     * Get total pendapatan per proyek per bulan
     */
    public static function getTotalPendapatanByProjectBulan(string $ccProjek, $bulan): float
    {
        return static::byProject($ccProjek)
                     ->byBulan($bulan)
                     ->pendapatan()
                     ->sum('nilai') ?? 0;
    }

    /**
     * Get total HPP per proyek per bulan
     */
    public static function getTotalHppByProjectBulan(string $ccProjek, $bulan): float
    {
        return static::byProject($ccProjek)
                     ->byBulan($bulan)
                     ->hpp()
                     ->sum('nilai') ?? 0;
    }
}
