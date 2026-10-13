<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = ['type', 'data'];

    protected $casts = ['data' => 'array'];
}
