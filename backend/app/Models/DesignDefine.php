<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignDefine extends Model
{
    protected $table = 'design_define';

    protected $fillable = [
        'project_id',
        'problem_statement',
        'learning_problem',
        'learning_objectives',
        'expected_outcomes',
        'knowledge_goals',
        'skill_goals',
        'attitude_goals',
        'success_criteria',
        'ai_notes',
    ];

    protected $casts = [
        'learning_objectives' => 'array',
        'ai_notes' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(DesignProject::class, 'project_id');
    }
}
