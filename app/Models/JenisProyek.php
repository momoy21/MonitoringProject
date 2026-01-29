<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisProyek extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'jenis_proyek';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'kode_jenis';

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'kode_jenis',
        'nama_jenis',
    ];

    /**
     * Get RAB Proyek for this Jenis Proyek
     */
    public function rabProyek(): HasMany
    {
        return $this->hasMany(RABProyek::class, 'jenis_proyek', 'kode_jenis');
    }

    /**
     * Get formatted label [Kode] Nama
     */
    public function getLabelAttribute(): string
    {
        return "[{$this->kode_jenis}] {$this->nama_jenis}";
    }
}
