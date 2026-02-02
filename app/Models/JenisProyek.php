<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisProyek extends Model
{
    use HasFactory;

    protected $table = 'jenisproyek';
    
    // Primary key menggunakan string (P1, P2, dst)
    protected $primaryKey = 'idjenisproyek';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'idjenisproyek', 
        'jenisproyek', 
        'status'
    ];

    /**
     * Scope untuk mengambil hanya data yang aktif
     * Cara pakai: JenisProyek::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }
}