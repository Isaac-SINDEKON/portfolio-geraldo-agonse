<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Formation extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'description',
        'objectives',
        'public_cible',
        'duree',
        'modalites',
        'programme',
        'icon',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'objectives' => 'array',
        'programme' => 'array',
        'sort_order' => 'integer',
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Formation $formation) {
            if (empty($formation->slug) && $formation->title) {
                $formation->slug = Str::slug($formation->title);
            }
        });
    }
}
