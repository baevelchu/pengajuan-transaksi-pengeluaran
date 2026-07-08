<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'description'];

    public function budget()
    {
        return $this->hasOne(Budget::class);
    }

    public function submissions()
    {
        return $this->hasMany(Pengajuan::class);
    }
}
