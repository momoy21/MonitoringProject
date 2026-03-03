<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderPenugasan extends Model
{
    protected $table = 'header_penugasan';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'IDPenugasan'; 

    protected $fillable = [
        'IDPenugasan',
        'cost_center',
        'id_project',
        'no_urut',
        'NoSurat',
        'PejabatTandatangan',
        'Pengusul',
        'Status',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENGAJUAN = 'P';
    public const STATUS_APPROVE = 'A';

    public const STATUS_OPTIONS = [
        'P' => 'Pengajuan',
        'A' => 'Approved',
    ];

    public function penugasan()
    {
        return $this->hasMany(Penugasan::class, 'IDPenugasan', 'IDPenugasan');
    }

    public function proyek()
    {
        return $this->belongsTo(HistoryProyek::class, 'id_project', 'id_project')
                    ->where('history_proyek.norut', $this->no_urut);
    }

    /**
     * Check if the header is in Pengajuan status
     */
    public function isPengajuan(): bool
    {
        return $this->Status === self::STATUS_PENGAJUAN;
    }

    /**
     * Check if the header is Approved
     */
    public function isApproved(): bool
    {
        return $this->Status === self::STATUS_APPROVE;
    }
}
