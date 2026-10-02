<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameAssignment extends Model
{
    protected $fillable = [
        'classroom_id',
        'game_id',
        'game_version_id',
        'start_at',
        'due_at',
        'max_attempts',
        'passing_score',
        'show_score',
        'allow_replay',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'due_at' => 'datetime',
        'max_attempts' => 'integer',
        'passing_score' => 'integer',
        'show_score' => 'boolean',
        'allow_replay' => 'boolean',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'assignment_id');
    }
}
