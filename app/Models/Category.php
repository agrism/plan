<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'emoji',
        'color',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->color) {
            'blue' => 'bg-blue-50 text-blue-800 border-blue-200',
            'rose', 'red' => 'bg-rose-50 text-rose-800 border-rose-200',
            'purple' => 'bg-purple-50 text-purple-800 border-purple-200',
            'amber', 'yellow' => 'bg-amber-50 text-amber-800 border-amber-200',
            'emerald', 'green' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'indigo' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
            'cyan' => 'bg-cyan-50 text-cyan-800 border-cyan-200',
            'orange' => 'bg-orange-50 text-orange-800 border-orange-200',
            'pink' => 'bg-pink-50 text-pink-800 border-pink-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public static function defaultCategories(): array
    {
        return [
            ['name' => 'Projekti', 'slug' => 'projekti', 'emoji' => '💼', 'color' => 'blue'],
            ['name' => 'Steidzami', 'slug' => 'steidzami', 'emoji' => '⚡', 'color' => 'rose'],
            ['name' => 'Attīstība', 'slug' => 'attistiba', 'emoji' => '🚀', 'color' => 'purple'],
            ['name' => 'Sanāksmes', 'slug' => 'sanaksmes', 'emoji' => '👥', 'color' => 'amber'],
            ['name' => 'Ikdienas', 'slug' => 'ikdienas', 'emoji' => '📋', 'color' => 'emerald'],
            ['name' => 'Citi', 'slug' => 'citi', 'emoji' => '✨', 'color' => 'slate'],
        ];
    }
}
