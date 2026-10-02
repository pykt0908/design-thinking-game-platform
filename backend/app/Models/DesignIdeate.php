<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignIdeate extends Model
{
    protected $table = 'design_ideate';

    protected $fillable = [
        'project_id',
        'game_concept',
        'game_genre',
        'theme',
        'story',
        'game_mechanics',
        'challenges',
        'missions',
        'rewards',
        'interaction_type',
        'difficulty',
        'duration_minutes',
        'ai_ideas',
    ];

    protected $casts = [
        'game_mechanics' => 'array',
        'ai_ideas' => 'array',
        'duration_minutes' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(DesignProject::class, 'project_id');
    }
}
