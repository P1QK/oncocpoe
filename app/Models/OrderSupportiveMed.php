<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSupportiveMed extends Model
{
    protected $table = 'order_supportive_meds';

    protected $fillable = [
        'or_id',
        'sm_code',
        'sm_name',
        'category',
        'dose',
        'route',
        'instruction',
        'sort_order',
    ];

    public function orderHeader(): BelongsTo
    {
        return $this->belongsTo(OrderHeader::class, 'or_id', 'or_id');
    }
}
