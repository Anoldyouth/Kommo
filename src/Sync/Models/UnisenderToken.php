<?php

namespace Sync\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnisenderToken extends Model
{
    protected $fillable = ['token'];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
