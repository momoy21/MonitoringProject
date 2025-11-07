<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeaderRAB extends Model
{
    protected $table = 'header_rab';
    protected $primaryKey = 'id_rab';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_rab',
        'id_project',
        'norut',
        'periode_rab',
        'lama'
    ];

    protected $casts = [
        'periode_rab' => 'date',
        'lama' => 'integer'
    ];

    /**
     * Relationship with HistoryProyek
     * Using manual query since Laravel doesn't support composite foreign keys directly
     */
    public function historyProyek()
    {
        return $this->hasOne(HistoryProyek::class, 'id_project', 'id_project')
                    ->where('norut', $this->norut);
    }

    /**
     * Relationship dengan DetailRAB
     */
    public function detailRABs()
    {
        return $this->hasMany(DetailRAB::class, 'id_rab', 'id_rab');
    }

    /**
     * Get the route key name for the model
     */
    public function getRouteKeyName()
    {
        return 'id_rab';
    }
}
