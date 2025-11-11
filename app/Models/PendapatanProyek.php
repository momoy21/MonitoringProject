<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendapatanProyek extends Model
{
    protected $table = 'pendapatan_proyek';

    // Composite primary key: norut, id_project, no_pendapatan, no_dokumen
    protected $primaryKey = ['norut', 'id_project', 'no_pendapatan', 'no_dokumen'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'norut',
        'id_project',
        'no_pendapatan',
        'no_dokumen',
        'no_ba',
        'tanggal',
        'periode_mulai',
        'periode_akhir',
        'nilai_pendapatan',
        'file_ba'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'periode_mulai' => 'date',
        'periode_akhir' => 'date',
        'nilai_pendapatan' => 'decimal:2'
    ];

    /**
     * Boot method to auto-generate no_pendapatan
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pendapatan) {
            // Auto-generate no_pendapatan if not set
            if (empty($pendapatan->no_pendapatan)) {
                $pendapatan->no_pendapatan = self::generateNoPendapatan(
                    $pendapatan->id_project,
                    $pendapatan->norut
                );
            }

            // Set tanggal to today if not set
            if (empty($pendapatan->tanggal)) {
                $pendapatan->tanggal = now()->format('Y-m-d');
            }
        });
    }

    /**
     * Relationship with BeritaAcaraProject
     */
    public function beritaAcara(): BelongsTo
    {
        return $this->belongsTo(BeritaAcaraProject::class, ['norut', 'id_project', 'no_ba'], ['norut', 'id_project', 'no_ba']);
    }

    /**
     * Relationship with HistoryProyek
     */
    public function historyProyek()
    {
        return $this->hasOne(HistoryProyek::class, 'id_project', 'id_project')
                    ->where('norut', $this->norut);
    }

    /**
     * Override setKeysForSaveQuery for composite key
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where('norut', $this->getAttribute('norut'))
              ->where('id_project', $this->getAttribute('id_project'))
              ->where('no_pendapatan', $this->getAttribute('no_pendapatan'))
              ->where('no_dokumen', $this->getAttribute('no_dokumen'));

        return $query;
    }

    /**
     * Get formatted nilai_pendapatan
     */
    public function getNilaiPendapatanFormattedAttribute()
    {
        if (!$this->nilai_pendapatan || $this->nilai_pendapatan == 0) {
            return '-';
        }

        $cleanValue = preg_replace('/[^0-9.]/', '', $this->nilai_pendapatan);
        $nilai = (float) $cleanValue;

        if ($nilai <= 0) {
            return '-';
        }

        return 'Rp ' . number_format($nilai, 2, ',', '.');
    }

    /**
     * Generate no_pendapatan (Format: PP + YYYY + sequential 001)
     * Each id_project + norut combination starts from 001
     */
    public static function generateNoPendapatan($idProject, $norut)
    {
        $year = date('Y');
        $prefix = 'PP' . $year;

        // Get max sequence for this id_project + norut combination
        $lastPendapatan = self::where('id_project', $idProject)
            ->where('norut', $norut)
            ->where('no_pendapatan', 'LIKE', $prefix . '%')
            ->orderBy('no_pendapatan', 'desc')
            ->first();

        if ($lastPendapatan) {
            $lastSequence = (int) substr($lastPendapatan->no_pendapatan, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix . $newSequence;
    }

    /**
     * Get the value of the model's primary key for composite key
     */
    public function getKey()
    {
        $keys = [];
        foreach ((array) $this->primaryKey as $key) {
            $keys[$key] = $this->getAttribute($key);
        }
        return $keys;
    }

    /**
     * Get next norut_display (for display purposes, newest first)
     */
    public static function getNextNorutDisplay($idProject, $norut, $noBA)
    {
        $count = self::where('id_project', $idProject)
                     ->where('norut', $norut)
                     ->where('no_ba', $noBA)
                     ->count();

        return $count + 1;
    }

    /**
     * Scope to filter by BA
     */
    public function scopeByBA($query, $idProject, $norut, $noBA)
    {
        return $query->where('id_project', $idProject)
                     ->where('norut', $norut)
                     ->where('no_ba', $noBA);
    }
}
