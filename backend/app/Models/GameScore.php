<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameScore extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'student_id',
        'total_score',
        'max_possible_score',
        'percentage',
        'passed',
        'created_at',
    ];

    protected $casts = [
        'total_score' => 'integer',
        'max_possible_score' => 'integer',
        'percentage' => 'float',
        'passed' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
