<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegimenSupportiveMed extends Model
{
    protected $table = 'regimen_supportive_meds';

    protected $fillable = [
        'rh_id',
        'sm_code',
        'override_dose',
        'override_route',
        'override_instruction',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function supportiveMed(): BelongsTo
    {
        return $this->belongsTo(SupportiveMed::class, 'sm_code', 'sm_code');
    }
}
