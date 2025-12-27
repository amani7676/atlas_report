<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'description'];

    protected $withCount = ['reports']; // این خط را اضافه کنید

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
