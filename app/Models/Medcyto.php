<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medcyto extends Model
{
    protected $table = 'medcyto';
    protected $primaryKey = 'dg_code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dg_code',
        'dg_name',
        'dg_brand',
        'dg_price',
        'dg_saleprc',
        'dg_conc',
        'dg_vol',
        'dg_recon',
        'dg_precaution',
        'dg_exprecon',
        'dg_expdilute',
        'dg_time',
        'dg_fullname',
    ];
}
