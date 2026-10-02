<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Game extends Model
{
    protected $fillable = [
        'teacher_id',
        'project_id',
        'public_id',
        'title',
        'description',
        'theme',
        'genre',
        'cover_image',
        'status',
        'current_version_id',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public static function generateUniquePublicId(): string
    {
        do {
            $code = 'GME-' . strtoupper(Str::random(5));
        } while (self::where('public_id', $code)->exists());

        return $code;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(DesignProject::class, 'project_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(GameVersion::class);
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'current_version_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(GameAssignment::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }
}
