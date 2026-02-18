<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisProyek extends Model
{
    use HasFactory;

    protected $table = 'jenis_proyek';
    protected $primaryKey = 'kode_jenis';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'kode_jenis',
        'nama_jenis',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }
}
