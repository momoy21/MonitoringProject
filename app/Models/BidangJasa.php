<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidangJasa extends Model
{
    use HasFactory;

    protected $table = 'bidangjasa';
    protected $primaryKey = 'id_bidjasa';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_bidjasa',
        'desc_bidjasa',
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
        $lastRecord = self::orderBy('id_bidjasa', 'desc')->first();

        if (!$lastRecord) {
            return '01';
        }

        $lastId = (int) $lastRecord->id_bidjasa;
        $nextId = $lastId + 1;

        return str_pad($nextId, 2, '0', STR_PAD_LEFT);
    }

    // Search scope
    public function scopeSearch($query, $search)
    {
        return $query->where('id_bidjasa', 'like', "%{$search}%")
                     ->orWhere('desc_bidjasa', 'like', "%{$search}%");
    }

    // Boot method to auto-generate ID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_bidjasa)) {
                $model->id_bidjasa = self::generateNextId();
            }
        });
    }

    // Get the route key for the model.
    public function getRouteKeyName()
    {
        return 'id_bidjasa';
    }
}
