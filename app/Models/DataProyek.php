<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataProyek extends Model
{
    protected $table = 'data_proyek';

    // Primary key is not auto-incrementing as it's a custom format
    protected $primaryKey = 'id_project';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_project',
        'dokumen_io',
        'cost_center',
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
     */
    public function getDokumenPathAttribute($value)
    {
        if (is_array($value)) {
            return $value[0] ?? null;
        }
        return $value;
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

    public function historyProyek(): HasMany
    {
        return $this->hasMany(HistoryProyek::class, 'id_project', 'id_project');
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

    // Accessor for status badge
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

    // Keterangan: 1=Kontrak Induk, 2=Bukan Kontrak Induk

    // Accessor for formatted nilai proyek (following DataPeluang pattern)
    public function getNilaiProyekFormattedAttribute()
    {
        if (!$this->nilai_proyek || $this->nilai_proyek == 0 || $this->nilai_proyek === '') {
            return '-';
        }

        // Convert to float for formatting
        $nilai = (float) $this->nilai_proyek;

        if ($nilai <= 0) {
            return '-';
        }

        return 'Rp ' . number_format($nilai, 0, ',', '.');
    }
}
