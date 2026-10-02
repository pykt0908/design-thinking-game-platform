<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignEmpathize extends Model
{
    protected $table = 'design_empathize';

    protected $fillable = [
        'project_id',
        'target_learner',
        'age_group',
        'grade_level',
        'subject',
        'learning_context',
        'learner_characteristics',
        'existing_knowledge',
        'interests',
        'learning_difficulties',
        'pain_points',
        'learning_environment',
        'device_availability',
        'ai_notes',
    ];

    protected $casts = [
        'ai_notes' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(DesignProject::class, 'project_id');
    }
}
