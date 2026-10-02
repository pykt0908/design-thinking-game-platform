<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameVersion extends Model
{
    protected $fillable = [
        'game_id',
        'version_number',
        'schema_data',
        'changelog',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
