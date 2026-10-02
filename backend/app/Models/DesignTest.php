<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignTest extends Model
{
    protected $table = 'design_tests';

    protected $fillable = [
        'project_id',
        'test_date',
        'test_users_count',
        'observations',
        'recorded_bugs',
        'difficulty_rating',
        'feedback_summary',
        'ai_recommendations',
    ];

    protected $casts = [
        'test_date' => 'date',
        'test_users_count' => 'integer',
        'difficulty_rating' => 'integer',
        'recorded_bugs' => 'array',
        'ai_recommendations' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(DesignProject::class, 'project_id');
    }
}
