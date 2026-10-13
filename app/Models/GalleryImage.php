<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    protected $fillable = ['image_path', 'caption', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        return $this->image_path
            ? asset('storage/'.$this->image_path)
            : null;
    }
}
