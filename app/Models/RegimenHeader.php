<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegimenHeader extends Model
{
    protected $table = 'reg_h';
    protected $primaryKey = 'rh_id';
    public $timestamps = false;

    protected $fillable = [
        'rh_code',
        'rh_used',
        'rh_created',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(RegimenDetail::class, 'rd_id', 'rh_id')->orderBy('rd_orderno');
    }

    public function supportiveMeds(): HasMany
    {
        return $this->hasMany(RegimenSupportiveMed::class, 'rh_id', 'rh_id');
    }
}
