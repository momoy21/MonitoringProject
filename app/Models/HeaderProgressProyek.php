<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeaderProgressProyek extends Model
{
    protected $table = 'header_progress_proyek';
    protected $primaryKey = 'id_progress';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_progress',
        'id_rab',
        'id_project',
        'norut',
        'periode_mulai',
        'lama',
        'periode_akhir'
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_akhir' => 'date',
        'lama' => 'integer'
    ];

    /**
     * Relationship with HeaderRAB
     */
    public function headerRAB(): BelongsTo
    {
        return $this->belongsTo(HeaderRAB::class, 'id_rab', 'id_rab');
    }

    /**
     * Relationship with HistoryProyek
     */
    public function historyProyek()
    {
        return $this->hasOne(HistoryProyek::class, 'id_project', 'id_project')
                    ->where('norut', $this->norut);
    }

    /**
     * Get the route key name for the model
     */
    public function getRouteKeyName()
    {
        return 'id_progress';
    }
}
