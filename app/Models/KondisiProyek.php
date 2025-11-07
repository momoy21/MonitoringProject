<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KondisiProyek extends Model
{
    use HasFactory;

    protected $table = 'kondisiproyek';
    protected $primaryKey = 'id_kondisi_proyek';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_kondisi_proyek',
        'desc_kondisi_proyek',
        'status',
    ];
    // Scope untuk hanya data aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Generate next ID automatically
    public static function generateNextId()
    {
        $lastRecord = self::orderBy('id_kondisi_proyek', 'desc')->first();

        if (!$lastRecord) {
            return 'K1';
        }

        $lastIdNumber = (int) str_replace('K', '', $lastRecord->id_kondisi_proyek);
        $nextIdNumber = $lastIdNumber + 1;

        return 'K' . $nextIdNumber;
    }

    // Search scope
    public function scopeSearch($query, $search)
    {
        return $query->where('id_kondisi_proyek', 'like', "%{$search}%")
                     ->orWhere('desc_kondisi_proyek', 'like', "%{$search}%");
    }

    // Boot method to auto-generate ID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_kondisi_proyek)) {
                $model->id_kondisi_proyek = self::generateNextId();
            }
        });
    }

    // Get the route key for the model.
    public function getRouteKeyName()
    {
        return 'id_kondisi_proyek';
    }
}
