<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope untuk hanya data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    /**
     * Scope untuk pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('kode_divisi', 'like', "%{$search}%")
                     ->orWhere('nama_divisi', 'like', "%{$search}%");
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'kode_divisi';
    }

    /**
     * Relasi ke RABProyek
     */
    public function rabProyek()
    {
        return $this->hasMany(RABProyek::class, 'divisi', 'kode_divisi');
    }
}
