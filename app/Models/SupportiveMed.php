<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportiveMed extends Model
{
    protected $table = 'supportive_meds';

    protected $fillable = [
        'sm_code',
        'sm_name',
        'category',
        'default_dose',
        'dose_options',
        'default_route',
        'default_instruction',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'dose_options' => 'array',
            'is_active'    => 'boolean',
        ];
    }
}
