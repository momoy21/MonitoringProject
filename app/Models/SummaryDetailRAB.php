<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryDetailRAB extends Model
{
    protected $table = 'summary_rab_detail';

    // Use composite primary key
    protected $primaryKey = ['id_rab', 'id_summary_rab'];
    public $incrementing = false;
    protected $keyType = 'string'; // Since id_rab is string

    protected $fillable = [
        'id_rab',
        'id_summary_rab',
        'idsummary',
        'nilai'
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'id_summary_rab' => 'integer'
    ];

    /**
     * Set the keys for a save update query.
     * Required for composite primary key support
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the value of the model's primary key.
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    /**
     * Relationship dengan HeaderRAB
     */
    public function headerRAB()
    {
        return $this->belongsTo(HeaderRAB::class, 'id_rab', 'id_rab');
    }

    /**
     * Relationship dengan SummaryRAB
     */
    public function summaryRAB()
    {
        return $this->belongsTo(SummaryRAB::class, 'idsummary', 'idsummary');
    }

    /**
     * Generate ID Summary RAB berikutnya untuk ID RAB tertentu
     */
    public static function generateNextIdSummaryRAB($idRAB)
    {
        $lastSummary = self::where('id_rab', $idRAB)
                         ->orderBy('id_summary_rab', 'desc')
                         ->first();

        if (!$lastSummary) {
            return 1;
        }

        return $lastSummary->id_summary_rab + 1;
    }

    /**
     * Scope untuk mendapatkan data berdasarkan ID RAB
     */
    public function scopeByIdRAB($query, $idRAB)
    {
        return $query->where('id_rab', $idRAB);
    }

    /**
     * Scope untuk pengurutan berdasarkan id_summary_rab
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('id_summary_rab', 'asc');
    }

    /**
     * Get formatted nilai
     */
    public function getFormattedNilaiAttribute()
    {
        if ($this->nilai === null || $this->nilai === 0) {
            return '-';
        }

        return 'Rp ' . number_format((float)$this->nilai, 0, ',', '.');
    }
}
