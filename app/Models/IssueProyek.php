<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueProyek extends Model
{
    protected $table = 'issue_proyek';

    // Composite primary key
    protected $primaryKey = ['norut', 'id_project', 'no_issue'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'norut',
        'id_project',
        'no_issue',
        'tanggal',
        'issue',
        'mitigasi',
        'status'
    ];

    protected $casts = [
        'tanggal' => 'datetime'
    ];

    protected $attributes = [
        'issue' => 'Tidak ada issue',
        'mitigasi' => 'Tidak ada mitigasi',
    ];

    /**
     * Relationship with HistoryProyek
     */
    public function historyProyek(): BelongsTo
    {
        return $this->belongsTo(HistoryProyek::class, ['norut', 'id_project'], ['norut', 'id_project']);
    }

    /**
     * Override the default key name for composite key
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where('norut', $this->getAttribute('norut'))
              ->where('id_project', $this->getAttribute('id_project'))
              ->where('no_issue', $this->getAttribute('no_issue'));

        return $query;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status === 'C' ? 'Close' : 'Open';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'C' ? 'bg-success' : 'bg-warning';
    }
}
