<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'category_id',
        'asset_pack_id',
        'file_path',
        'preview_url',
        'style',
        'theme',
        'metadata',
        'license',
        'author',
        'is_public',
        'uploaded_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function assetPack(): BelongsTo
    {
        return $this->belongsTo(AssetPack::class, 'asset_pack_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
