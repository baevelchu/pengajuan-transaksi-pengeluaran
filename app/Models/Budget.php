<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['category_id', 'total_budget', 'used_budget'];

    protected $casts = [
        'total_budget' => 'decimal:2',
        'used_budget' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getSisaBudgetAttribute()
    {
        return $this->total_budget - $this->used_budget;
    }

    public function isCukup(float $nilai): bool
    {
        return $this->sisa_budget >= $nilai;
    }
}
