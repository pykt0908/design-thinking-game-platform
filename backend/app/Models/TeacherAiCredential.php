<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAiCredential extends Model
{
    protected $fillable = [
        'teacher_id',
        'provider',
        'encrypted_api_key',
        'model',
        'base_url',
        'is_active',
    ];

    protected $casts = [
        'encrypted_api_key' => 'encrypted',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'encrypted_api_key',
    ];

    protected $appends = [
        'masked_key',
    ];

    public function getMaskedKeyAttribute(): string
    {
        $raw = $this->encrypted_api_key ?? '';
        if (strlen($raw) < 8) {
            return '••••••••';
        }
        $prefix = substr($raw, 0, 3);
        $suffix = substr($raw, -4);
        return $prefix . '-••••••••••••' . $suffix;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
