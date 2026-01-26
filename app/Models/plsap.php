<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plsap extends Model
{
    protected $table = 'plsap';

    protected $fillable = [
        'internal_order',
        'cc_projek',
        'description_io',
        'cost_element',
        'description_ce',
        'amount_local',
        'posting_date',
        'profit_center',
        'description_pca',
        'source_file',
        'imported_at',
    ];

    protected $casts = [
        'amount_local' => 'decimal:2',
        'posting_date' => 'date',
        'imported_at' => 'datetime',
    ];
}