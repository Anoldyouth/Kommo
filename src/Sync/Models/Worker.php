<?php

namespace Sync\Models;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $fillable = ['type', 'name'];
}