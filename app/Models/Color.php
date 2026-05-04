<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'hex_code'])]
class Color extends Model
{
    use SoftDeletes;

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }
}
