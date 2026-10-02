<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetPack extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'theme',
        'cover_image',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'asset_pack_id');
    }
}
