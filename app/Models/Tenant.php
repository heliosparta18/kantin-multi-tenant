<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bank_account_number' => 'encrypted',
        ];
    }
}
