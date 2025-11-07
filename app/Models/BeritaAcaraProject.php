<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeritaAcaraProject extends Model
{
    protected $table = 'berita_acara_project';

    // Composite primary key
    protected $primaryKey = ['norut', 'id_project', 'no_ba'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'norut',
        'id_project',
        'no_ba',
        'desc',
        'periode_mulai',
        'periode_akhir',
        'nilai_ba',
        'status'
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_akhir' => 'date',
        'nilai_ba' => 'decimal:2'
    ];

    /**
     * Relationship with HistoryProyek
     */
    public function historyProyek()
    {
        return $this->hasOne(HistoryProyek::class, 'id_project', 'id_project')
                    ->where('norut', $this->norut);
    }

    /**
     * Override the default key name for composite key
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where('norut', $this->getAttribute('norut'))
              ->where('id_project', $this->getAttribute('id_project'))
              ->where('no_ba', $this->getAttribute('no_ba'));

        return $query;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            '01' => 'Draft',
            '02' => 'Review',
            '03' => 'Approve',
            '04' => 'Pending'
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            '01' => 'bg-secondary',
            '02' => 'bg-warning',
            '03' => 'bg-success',
            '04' => 'bg-info'
        ];

        return $classes[$this->status] ?? 'bg-secondary';
    }
}
