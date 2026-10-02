<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'question_id',
        'question_text',
        'selected_answer',
        'is_correct',
        'score_awarded',
        'time_spent_seconds',
        'created_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'score_awarded' => 'integer',
        'time_spent_seconds' => 'integer',
        'created_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }
}
