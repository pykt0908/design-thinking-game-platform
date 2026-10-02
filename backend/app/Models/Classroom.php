<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Classroom extends Model
{
    protected $fillable = [
        'teacher_id',
        'name',
        'code',
        'description',
        'academic_year',
        'semester',
        'cover_image',
        'theme_color',
        'status',
    ];

    public static function generateUniqueCode(): string
    {
        do {
            $code = 'DTG-' . strtoupper(Str::random(5));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_students', 'classroom_id', 'student_id')
            ->withPivot('joined_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(GameAssignment::class);
    }
}
