<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBalance extends Model
{
    protected $fillable = ['saldo'];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public static function current(): self
    {
        return static::first() ?? static::create(['saldo' => 0]);
    }
}
