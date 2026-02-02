<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RABPleno extends Model
{
    use HasFactory;

    protected $table = 'rab_pleno';
    protected $primaryKey = 'nopengajuan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nopengajuan', 'tglinput', 'dokumen_io', 'cost_center', 
        'namaproject', 'bidang_jasa', 'idkonsumen', 'nilaiproyek', 
        'marginrkap', 'marginpleno', 'keterangan', 'progress', 
        'hasil_pleno', 'catatan', 'hasilupload'
    ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            // Logika Hasil Pleno: Tercapai (TR) atau Tidak Tercapai (TT)
            $model->hasil_pleno = ($model->marginpleno >= $model->marginrkap) ? 'TR' : 'TT';
        });
    }
}