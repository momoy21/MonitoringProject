<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penugasan extends Model
{
    protected $table = 'penugasan';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

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

    protected $casts = [
        'Periodeawal'  => 'date',
        'Periodeakhir' => 'date',
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
