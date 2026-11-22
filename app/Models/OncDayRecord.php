<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OncDayRecord extends Model
{
    protected $table = 'onc_dayrecord';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'or_check',
        'or_prep',
        'or_dispatch',
        'or_prepared_at',
        'or_dispatched_at',
        'or_dispatched_by',
        'or_send',
        'or_administered_at',
        'or_administered_by',
        'or_id',
        'or_medcode',
        'or_meddose',
        'or_medunit',
        'or_soln',
        'or_medroute',
        'or_solvol',
        'or_additives',
        'or_medtime',
        'or_orderno',
        'or_bag_group',
        'or_daterec',
        'or_start',
        'or_stop',
        'or_working',
        'or_totalvol',
        'or_text',
        'or_recstamp',
        'or_hold',
        'or_holdnote',
        'or_holddate',
        'or_holdstop',
        'or_discont',
    ];

    protected function casts(): array
    {
        return [
            'or_additives' => 'array',
        ];
    }

    public function orderHeader(): BelongsTo
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
