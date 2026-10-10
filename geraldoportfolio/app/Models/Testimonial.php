<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['author', 'fonction', 'content', 'photo', 'sort_order', 'active'];

    protected $casts = [
        'sort_order' => 'integer',
        'active' => 'boolean',
    ];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }
}
