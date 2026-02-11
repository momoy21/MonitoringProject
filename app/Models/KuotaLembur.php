<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuotaLembur extends Model
{
    protected $table = 'kuota_lembur';

    protected $fillable = [
        'cost_center',
        'dok_io',
        'nik',
        'bulan',
        'periode_awal',
        'periode_akhir',
        'jml_wd',
        'jml_we',
        'jml_hn',
        'status',
    ];

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'bulan' => 'integer',
        'jml_wd' => 'integer',
        'jml_we' => 'integer',
        'jml_hn' => 'integer',
    ];

    /**
     * Relationship to Karyawan
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    /**
     * Relationship to DataProyek via cost_center
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(DataProyek::class, 'cost_center', 'cost_center');
    }

    /* -------------------------------------------------------------------------- */
    /* LOGIC STATUS SESUAI BPS 22                         */
    /* -------------------------------------------------------------------------- */

    /**
     * Cek apakah data masih Draft (Belum terkirim)
     */
    public function getIsDraftAttribute(): bool
    {
        return is_null($this->status);
    }

    /**
     * Cek apakah data sudah Terkirim (Final)
     */
    public function getIsTerkirimAttribute(): bool
    {
        return $this->status === 'F';
    }

    /**
     * Scope untuk mengambil data yang sudah terkirim ('F')
     */
    public function scopeTerkirim($query)
    {
        return $query->where('status', 'F');
    }

    /**
     * Scope untuk mengambil data draft (NULL)
     */
    public function scopeDraft($query)
    {
        return $query->whereNull('status');
    }

    /**
     * Get status label untuk Tampilan
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'F') {
            return 'Terkirim ke Path';
        }
        return 'Draft / Belum Terkirim';
    }

    /**
     * Get status badge class untuk UI (Bootstrap)
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'F' => 'badge bg-success',    
            null => 'badge bg-warning',   
            default => 'badge bg-secondary',
        };
    }
}
