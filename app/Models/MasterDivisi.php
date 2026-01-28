<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterDivisi extends Model
{
    use HasFactory;

    protected $table = 'master_divisi';
    protected $primaryKey = 'kode_divisi';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'kode_divisi',
        'nama_divisi',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }
}