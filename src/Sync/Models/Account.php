<?php

namespace Sync\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    protected $fillable = ['account_name', 'access_token', 'unisender_token_id'];

    public function token(): BelongsTo
    {
        return $this->belongsTo(UnisenderToken::class, 'unisender_token_id', 'id');
    }
}
