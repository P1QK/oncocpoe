<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChemoAdditive extends Model
{
    protected $table = 'chemo_additives';

    protected $fillable = [
        'ad_code',
        'ad_name',
        'default_dose',
        'default_unit',
        'default_vol_ml',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_vol_ml' => 'float',
            'is_active'      => 'boolean',
        ];
    }
}
