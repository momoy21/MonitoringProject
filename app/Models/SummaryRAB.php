<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryRAB extends Model
{
    protected $table = 'summary_rab';
    protected $primaryKey = 'idsummary';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idsummary',
        'ketsummaryrab',
        'norutsummary',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method untuk auto-generate idsummary
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->idsummary)) {
                $model->idsummary = static::generateNextIdSummary();
            }

            // Set default status jika belum ada
            if (empty($model->status)) {
                $model->status = 'A';
            }
        });
    }

    /**
     * Generate next idsummary (0001, 0002, 0003, ...)
     */
    public static function generateNextIdSummary()
    {
        $lastRecord = static::orderBy('idsummary', 'desc')->first();

        if (!$lastRecord) {
            return '0001';
        }

        $lastNumber = (int) $lastRecord->idsummary;
        $nextNumber = $lastNumber + 1;

        return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Custom ordering: norutsummary ASC, created_at ASC
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('norutsummary', 'asc')
                     ->orderBy('created_at', 'asc');
    }

    /**
     * Scope untuk hanya data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    /**
     * Scope untuk hanya data non-aktif
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'N');
    }

    /**
     * Accessor untuk status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status === 'A' ? 'Aktif' : 'Non Aktif';
    }

    public function summaryDetails()
    {
        return $this->hasMany(SummaryDetailRAB::class, 'idsummary', 'idsummary');
    }
}
