<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignPrototype extends Model
{
    protected $table = 'design_prototype';

    protected $fillable = [
        'project_id',
        'scene_outline',
        'character_roles',
        'core_rules',
        'feedback_mechanisms',
        'ai_notes',
    ];

    protected $casts = [
        'scene_outline' => 'array',
        'character_roles' => 'array',
        'core_rules' => 'array',
        'ai_notes' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(DesignProject::class, 'project_id');
    }
}
