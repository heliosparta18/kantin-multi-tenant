<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Table extends Model
{
    protected $guarded = [];

    /**
     * @return BelongsTo<Canteen, $this>
     */
    public function canteen(): BelongsTo
    {
        return $this->belongsTo(Canteen::class);
    }
}
