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
        'NoSurat',
        'PejabatTandatangan',
    ];

    public function penugasan()
    {
        return $this->hasMany(Penugasan::class, 'IDPenugasan', 'IDPenugasan');
    }

    public function proyek()
    {
        return $this->belongsTo(HistoryProyek::class, 'cost_center', 'cost_center');
    }
}
