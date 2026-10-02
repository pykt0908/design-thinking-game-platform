<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DesignProject extends Model
{
    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'subject',
        'grade_level',
        'game_mode',
        'game_genre',
        'theme_pack',
        'current_step',
        'status',
    ];

    protected $casts = [
        'current_step' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function empathize(): HasOne
    {
        return $this->hasOne(DesignEmpathize::class, 'project_id');
    }

    public function define(): HasOne
    {
        return $this->hasOne(DesignDefine::class, 'project_id');
    }

    public function ideate(): HasOne
    {
        return $this->hasOne(DesignIdeate::class, 'project_id');
    }

    public function prototype(): HasOne
    {
        return $this->hasOne(DesignPrototype::class, 'project_id');
    }

    public function test(): HasOne
    {
        return $this->hasOne(DesignTest::class, 'project_id');
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'project_id');
    }
}
