<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRequest extends Model
{
    protected $fillable = [
        'teacher_id',
        'project_id',
        'service_type',
        'prompt_tokens',
        'completion_tokens',
        'status',
        'error_message',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(DesignProject::class, 'project_id');
    }
}
