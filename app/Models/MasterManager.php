<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterManager extends Model
{
    use HasFactory;

    protected $table = 'master_manager';
    protected $primaryKey = 'nik';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nik',
        'nama',
        'status',
        'kode_divisi'
    ];

    // Relationship with MasterDivisi
    public function divisi()
    {
        return $this->belongsTo(MasterDivisi::class, 'kode_divisi', 'kode_divisi');
    }

    // Scope
    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }

    // Accessor
    public function getNamaLengkapAttribute()
    {
        return $this->nik . ' - ' . $this->nama;
    }
}
