<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegimenDetail extends Model
{
    protected $table = 'reg_d';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'rd_id',
        'rd_medcode',
        'rd_meddose',
        'rd_medunit',
        'rd_sol',
        'rd_solvol',
        'rd_medroute',
        'rd_medtime',
        'rd_start',
        'rd_duration',
    ];

    public function regimen(): BelongsTo
    {
        return $this->belongsTo(RegimenHeader::class, 'rd_id', 'rh_id');
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medcyto::class, 'rd_medcode', 'dg_code');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'rd_medunit', 'un_code');
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class, 'rd_sol', 'so_code');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'rd_medroute', 'rt_code');
    }

    public function timing(): BelongsTo
    {
        return $this->belongsTo(Timing::class, 'rd_medtime', 'tm_code');
    }
}
