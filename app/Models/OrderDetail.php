<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $table = 'order_d';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'or_id',
        'or_medcode',
        'or_std_dose',
        'or_reduce_pct',
        'or_meddose',
        'or_medunit',
        'or_soln',
        'or_medroute',
        'or_solvol',
        'or_medtime',
        'or_hold',
        'or_holddate',
        'or_holdnote',
        'or_discont',
        'or_orderno',
        'or_start',
        'or_stop',
        'or_duration',
        'or_working',
        'or_totalvol',
        'or_addition',
        'or_text',
        'or_cf',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(OrderHeader::class, 'or_id', 'or_id');
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medcyto::class, 'or_medcode', 'dg_code');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'or_medunit', 'un_code');
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class, 'or_soln', 'so_code');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'or_medroute', 'rt_code');
    }

    public function timing(): BelongsTo
    {
        return $this->belongsTo(Timing::class, 'or_medtime', 'tm_code');
    }
}
