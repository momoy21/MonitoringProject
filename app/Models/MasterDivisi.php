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
<<<<<<< HEAD
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope untuk hanya data aktif
     */
=======
        'status'
    ];

>>>>>>> f83ca6e4e425778f36642efa5004e211ccd97da0
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }
<<<<<<< HEAD

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
=======
}
>>>>>>> f83ca6e4e425778f36642efa5004e211ccd97da0
