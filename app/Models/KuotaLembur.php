<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuotaLembur extends Model
{
    use HasFactory;

    protected $table = 'kuota_lembur';
    
    // Composite primary key
    protected $primaryKey = ['cost_center', 'dok_io', 'nik', 'bulan'];
    public $incrementing = false;
    protected $keyType = 'string';

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status options
     */
    public const STATUS_OPTIONS = [
        null => 'Belum Terkirim',
        'F' => 'Sudah Terkirim',
    ];

    /**
     * Override setKeysForSaveQuery for composite key support
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    /**
     * Relation to Karyawan
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    /**
     * Relation to DataProyek via cost_center
     */
    public function proyek(): BelongsTo
    {
        return $this->belongsTo(DataProyek::class, 'cost_center', 'cost_center');
    }

    /**
     * Scope for unsynced data (status is null)
     */
    public function scopeUnsynced($query)
    {
        return $query->whereNull('status');
    }

    /**
     * Scope for synced data (status = F)
     */
    public function scopeSynced($query)
    {
        return $query->where('status', 'F');
    }

    /**
     * Scope for period filter
     */
    public function scopeInPeriod($query, $periodeAwal, $periodeAkhir)
    {
        return $query->where('periode_awal', '>=', $periodeAwal)
                     ->where('periode_akhir', '<=', $periodeAkhir);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_OPTIONS[$this->status] ?? 'Unknown';
    }

    /**
     * Get total jam lembur
     */
    public function getTotalJamAttribute(): int
    {
        return $this->jml_wd + $this->jml_we + $this->jml_hn;
    }
}
