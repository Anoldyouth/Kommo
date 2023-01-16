<?php

namespace Sync\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['kommo_contact_id', 'name', 'work_email'];
}
