<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'created_by_id',
        'assigned_to_id',
        'title',
        'description',
        'image_url',
        'category',
        'scheduled_date',
        'scheduled_time_slot',
        'is_completed',
        'sort_order',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(TaskReaction::class);
    }

    public function getCategoryEmojiAttribute(): string
    {
        return match ($this->category) {
            'projekti' => '💼',
            'steidzami' => '⚡',
            'attistiba' => '🚀',
            'sanaksmes' => '👥',
            'ikdienas' => '📋',
            default => '✨',
        };
    }

    public function getCategoryNameAttribute(): string
    {
        return match ($this->category) {
            'projekti' => 'Projekti',
            'steidzami' => 'Steidzami',
            'attistiba' => 'Attīstība',
            'sanaksmes' => 'Sanāksmes',
            'ikdienas' => 'Ikdienas',
            default => 'Citi',
        };
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        return match ($this->category) {
            'projekti' => 'bg-blue-50 text-blue-800 border-blue-200',
            'steidzami' => 'bg-rose-50 text-rose-800 border-rose-200',
            'attistiba' => 'bg-purple-50 text-purple-800 border-purple-200',
            'sanaksmes' => 'bg-amber-50 text-amber-800 border-amber-200',
            'ikdienas' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
