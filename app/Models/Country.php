<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    public $timestamps = false;

    protected $fillable = ['id', 'shortname', 'name', 'phonecode'];

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }
}
