<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penugasan extends Model
{
    protected $table = 'penugasan';

    // Gunakan IDPenugasan sebagai primary key
    protected $primaryKey = 'IDPenugasan';
    public $incrementing = false; // karena bukan auto increment
    protected $keyType = 'string'; // ID string, bukan integer

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

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'NIK', 'NIK');
    }

    public function proyek()
    {
        return $this->belongsTo(HistoryProyek::class, 'cost_center', 'cost_center');
    }
}
