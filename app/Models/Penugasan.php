<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penugasan extends Model
{
    protected $table = 'penugasan';

    /**
     * Composite Primary Key: IDPenugasan, cost_center, Norut, NIK
     * Laravel tidak mendukung composite key secara native untuk primaryKey,
     * jadi kita set incrementing = false dan gunakan custom method untuk find
     */
    protected $primaryKey = ['IDPenugasan', 'cost_center', 'Norut', 'NIK'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'IDPenugasan',
        'cost_center',
        'Norut',
        'NIK',
        'NoSurat',
        'Dokumen_IO',
        'Jabatan',
        'Periodeawal',
        'Periodeakhir',
        'Bobot',
        'Status',
        'Keterangan'
    ];

    /**
     * Override getKeyName untuk composite key
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Find record berdasarkan composite key
     */
    public static function findByCompositeKey($idPenugasan, $costCenter, $norut, $nik)
    {
        return static::where('IDPenugasan', $idPenugasan)
            ->where('cost_center', $costCenter)
            ->where('Norut', $norut)
            ->where('NIK', $nik)
            ->first();
    }

    /**
     * Delete record berdasarkan composite key
     */
    public static function deleteByCompositeKey($idPenugasan, $costCenter, $norut, $nik)
    {
        return static::where('IDPenugasan', $idPenugasan)
            ->where('cost_center', $costCenter)
            ->where('Norut', $norut)
            ->where('NIK', $nik)
            ->delete();
    }

    protected $casts = [
        'Periodeawal'  => 'date',
        'Periodeakhir' => 'date',
        'Bobot'        => 'decimal:2',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'NIK', 'nik');
    }

    public function proyek()
    {
        return $this->belongsTo(HistoryProyek::class, 'cost_center', 'cost_center');
    }
}
