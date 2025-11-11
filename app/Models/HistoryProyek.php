<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryProyek extends Model
{
    protected $table = 'history_proyek';

    // Disable auto-incrementing as we use composite primary key
    public $incrementing = false;

    // Define composite primary key
    protected $primaryKey = ['norut', 'id_project'];

    // Specify that primary key is not an integer
    protected $keyType = 'string';

    protected $fillable = [
        'norut',
        'id_project',
        'cost_center',
        'dokumen_io',
        'namaproject',
        'id_konsumen',
        'id_datapeluang',
        'id_bidjasa',
        'lokasi_proyek',
        'jarak_lokasi',
        'id_kondisi_proyek',
        'no_kontrak',
        'tgl_pengakuan',
        'tgl_kontrak',
        'start_kontrak',
        'finish_kontrak',
        'tgl_expire',
        'penanggung_jawab',
        'nilai_proyek',
        'status',
        'keterangan',
        'dokumen_path'
    ];

    protected $casts = [
        'tgl_pengakuan' => 'datetime',
        'tgl_kontrak' => 'datetime',
        'start_kontrak' => 'datetime',
        'finish_kontrak' => 'datetime',
        'tgl_expire' => 'datetime',
        'nilai_proyek' => 'string'
    ];

    /**
     * Accessor to ensure dokumen_path is always a string, not array
     * FIXED: Handle all edge cases properly
     */
    public function getDokumenPathAttribute($value)
    {
        // If it's already null or empty, return null
        if (empty($value)) {
            return null;
        }

        // If it's an array, get the first element
        if (is_array($value)) {
            return !empty($value[0]) ? $value[0] : null;
        }

        // If it's a string, return as is
        return $value;
    }

    /**
     * Mutator to ensure dokumen_path is stored as string
     */
    public function setDokumenPathAttribute($value)
    {
        // If it's an array, store only the first element
        if (is_array($value)) {
            $this->attributes['dokumen_path'] = !empty($value[0]) ? $value[0] : null;
        } else {
            $this->attributes['dokumen_path'] = $value;
        }
    }

    /**
     * Boot method to auto-generate norut when creating new history record
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($historyProyek) {
            // Auto-generate norut if not set
            if (empty($historyProyek->norut)) {
                // ALWAYS use id_project for norut calculation
                // Each id_project starts norut from 1
                if (!empty($historyProyek->id_project)) {
                    $historyProyek->norut = self::getNextNoUrutForProject($historyProyek->id_project);
                } else {
                    $historyProyek->norut = 1;
                }
            }
        });
    }

    // Relationships
    public function konsumen(): BelongsTo
    {
        return $this->belongsTo(Konsumen::class, 'id_konsumen', 'id_konsumen');
    }

    public function dataPeluang(): BelongsTo
    {
        return $this->belongsTo(DataPeluang::class, 'id_datapeluang', 'id_datapeluang');
    }

    public function bidangJasa(): BelongsTo
    {
        return $this->belongsTo(BidangJasa::class, 'id_bidjasa', 'id_bidjasa');
    }

    public function kondisiProyek(): BelongsTo
    {
        return $this->belongsTo(KondisiProyek::class, 'id_kondisi_proyek', 'id_kondisi_proyek');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(MasterManager::class, 'penanggung_jawab', 'nik');
    }

    // Relationship to parent proyek by id_project (not cost_center)
    public function parentProyek(): BelongsTo
    {
        return $this->belongsTo(DataProyek::class, 'id_project', 'id_project');
    }

    /**
     * Relationship to Header Progress Proyek
     */
    public function headerProgressProyek()
    {
        return $this->hasOne(HeaderProgressProyek::class, 'id_rab', 'id_rab');
    }

    /**
     * Relationship to Header RAB
     */
    public function headerRAB()
    {
        return $this->hasOne(HeaderRAB::class, 'id_project', 'id_project')
                    ->where('norut', $this->norut);
    }

    // Accessor for jarak lokasi description
    public function getJarakLokasiTextAttribute()
    {
        $jarakMap = [
            1 => 'Jarak 5KM - 10KM',
            2 => 'Jarak 21KM - 30KM',
            3 => 'Jarak 31KM - 40KM',
            4 => 'Jarak 41KM - 50KM',
            5 => 'Jarak 51KM - 60KM',
            6 => 'Jarak 11KM - 20KM'
        ];

        return $jarakMap[$this->jarak_lokasi] ?? null;
    }

    // Accessor for status description
    public function getStatusTextAttribute()
    {
        $statusMap = [
            'O' => 'Open',
            'I' => 'In Progress',
            'C' => 'Close',
            'P' => 'Pending',
            'F' => 'Finish Pekerjaan'
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'I' => 'badge bg-primary',
            'O' => 'badge bg-info',
            'C' => 'badge bg-success',
            'P' => 'badge bg-secondary',
            'F' => 'badge bg-warning',
            default => 'badge bg-secondary'
        };
    }

    public function getNilaiProyekFormattedAttribute()
    {
        if (!$this->nilai_proyek || $this->nilai_proyek == 0 || $this->nilai_proyek === '') {
            return '-';
        }

        // Clean any existing formatting
        $cleanValue = preg_replace('/[^0-9.]/', '', $this->nilai_proyek);
        $nilai = (float) $cleanValue;

        if ($nilai <= 0) {
            return '-';
        }

        return 'Rp ' . number_format($nilai, 0, ',', '.');
    }

    /**
     * Get next NoUrut for specific id_project
     * Each id_project has its own norut sequence starting from 1
     */
    public static function getNextNoUrutForProject($idProject)
    {
        // Use database locking to prevent race condition
        $maxNorut = self::where('id_project', $idProject)
                        ->lockForUpdate()
                        ->max('norut');

        $nextNorut = $maxNorut ? $maxNorut + 1 : 1;

        return $nextNorut;
    }

    /**
     * Get next NoUrut for specific cost_center (deprecated - kept for backward compatibility)
     * Note: Should use getNextNoUrutForProject instead
     */
    public static function getNextNoUrutForCostCenter($costCenter)
    {
        // Use database locking to prevent race condition
        $maxNorut = self::where('cost_center', $costCenter)
                        ->lockForUpdate()
                        ->max('norut');

        $nextNorut = $maxNorut ? $maxNorut + 1 : 1;

        return $nextNorut;
    }

    /**
     * Create new history proyek with auto norut
     */
    public static function createWithAutoNorut($data)
    {
        // Remove norut from data to let boot method handle it automatically
        unset($data['norut']);

        $result = self::create($data);

        return $result;
    }

    /**
     * Fix norut sequence for existing records in an id_project
     * Each id_project should have norut starting from 1
     */
    public static function fixNorutSequence($idProject)
    {
        $records = self::where('id_project', $idProject)
                      ->orderBy('created_at', 'asc') // Order by creation time to maintain chronological sequence
                      ->get();

        foreach ($records as $index => $record) {
            $newNorut = $index + 1;
            if ($record->norut !== $newNorut) {
                $record->update(['norut' => $newNorut]);
            }
        }
    }

    /**
     * Get the value of the model's primary key for composite key.
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
     * Set the keys for a save update query for composite key.
     */
    protected function setKeysForSaveQuery($query)
    {
        foreach ((array) $this->primaryKey as $key) {
            $query->where($key, '=', $this->getAttribute($key));
        }
        return $query;
    }
}
