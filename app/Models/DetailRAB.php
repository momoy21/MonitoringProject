<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DetailRAB extends Model
{
    protected $table = 'detail_rab';

    // Use composite primary key
    protected $primaryKey = ['id_rab', 'id_detail_rab'];
    public $incrementing = false;
    protected $keyType = 'string'; // Since id_rab is string

    protected $fillable = [
        'id_rab',
        'id_detail_rab',
        'id_spec',
        'bulan',
        'urutbln',
        'nilai'
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'urutbln' => 'integer',
        'id_detail_rab' => 'integer'
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
     * Relationship dengan SpesifikasiRAB
     */
    public function spesifikasiRAB()
    {
        return $this->belongsTo(SpesifikasiRAB::class, 'id_spec', 'id_spec');
    }

    /**
     * Generate ID Detail RAB berikutnya untuk ID RAB tertentu
     */
    public static function generateNextIdDetailRAB($idRAB)
    {
        $lastDetail = self::where('id_rab', $idRAB)
                         ->orderBy('id_detail_rab', 'desc')
                         ->first();

        if (!$lastDetail) {
            return 1;
        }

        return $lastDetail->id_detail_rab + 1;
    }

    /**
     * Generate bulan format "MMM YYYY" dari periode mulai dan urutan bulan
     */
    public static function generateBulanFormat($periodeMulai, $urutBulan)
    {
        // Parse periode mulai (format dd/mm/yyyy)
        $tanggalMulai = Carbon::createFromFormat('d/m/Y', $periodeMulai);

        // Hitung bulan target berdasarkan urutan
        // Bulan ke-0 adalah satu bulan sebelum periode mulai
        $targetMonth = $tanggalMulai->copy()->subMonth()->addMonths($urutBulan);

        // Format ke "Sep 2025" (contoh: Sep 2025)
        return $targetMonth->format('M Y');
    }

    /**
     * Scope untuk mendapatkan data berdasarkan ID RAB
     */
    public function scopeByIdRAB($query, $idRAB)
    {
        return $query->where('id_rab', $idRAB);
    }

    /**
     * Scope untuk pengurutan berdasarkan urutan bulan dan spesifikasi
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutbln', 'asc')
                    ->orderBy('id_spec', 'asc');
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
