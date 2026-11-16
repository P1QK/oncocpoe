<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderHeader extends Model
{
    protected $table = 'order_h';
    protected $primaryKey = 'or_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'or_id',
        'or_guid',
        'or_hn',
        'or_patname',
        'or_age',
        'or_sex',
        'or_wt',
        'or_ht',
        'or_bsa',
        'or_scr',
        'or_gfr',
        'or_reg_code',
        'or_cycle',
        'or_date',
        'or_prnflag',
        'or_chkflag',
        'or_prepflag',
        'or_sendflag',
        'or_ward',
        'or_daily',
        'or_hold',
        'or_holdnote',
        'or_holddate',
        'or_confirm',
        'or_adjust_percent',
        'or_adjust_reason',
        'or_doctor_id',
        'or_doctor_name',
        'or_pharmacist_id',
        'or_pharmacist_name',
        'or_confirmed_at',
        'or_imgpath',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'or_id', 'or_id')->orderBy('or_orderno');
    }

    public function dayRecords(): HasMany
    {
        return $this->hasMany(OncDayRecord::class, 'or_id', 'or_id')->orderBy('or_daterec')->orderBy('or_orderno');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'or_ward', 'wd_code');
    }
}
