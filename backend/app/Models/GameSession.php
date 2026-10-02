<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GameSession extends Model
{
    protected $fillable = [
        'student_id',
        'game_id',
        'game_version_id',
        'assignment_id',
        'session_token',
        'started_at',
        'completed_at',
        'duration_seconds',
        'score',
        'max_score',
        'progress_percent',
        'attempt_number',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer',
        'score' => 'integer',
        'max_score' => 'integer',
        'progress_percent' => 'integer',
        'attempt_number' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(GameAssignment::class, 'assignment_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(GameEvent::class, 'session_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(GameAnswer::class, 'session_id');
    }

    public function scoreRecord(): HasOne
    {
        return $this->hasOne(GameScore::class, 'session_id');
    }
}
