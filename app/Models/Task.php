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
        'links',
        'category',
        'category_id',
        'scheduled_date',
        'scheduled_time_slot',
        'is_completed',
        'sort_order',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'is_completed' => 'boolean',
        'links' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function categoryRelation(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
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
        if ($this->categoryRelation) {
            return $this->categoryRelation->emoji;
        }

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
        if ($this->categoryRelation) {
            return $this->categoryRelation->name;
        }

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
        if ($this->categoryRelation) {
            return $this->categoryRelation->badge_class;
        }

        return match ($this->category) {
            'projekti' => 'bg-blue-50 text-blue-800 border-blue-200',
            'steidzami' => 'bg-rose-50 text-rose-800 border-rose-200',
            'attistiba' => 'bg-purple-50 text-purple-800 border-purple-200',
            'sanaksmes' => 'bg-amber-50 text-amber-800 border-amber-200',
            'ikdienas' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    /**
     * Extracts YouTube Video ID if the given URL is a YouTube video/short.
     */
    public static function extractYouTubeId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Returns structured list of links with YouTube embed info and domain names.
     *
     * @return array<array{url: string, youtube_id: ?string, embed_url: ?string, domain: string}>
     */
    public function getProcessedLinksAttribute(): array
    {
        $rawLinks = $this->links ?? [];
        if (!is_array($rawLinks)) {
            return [];
        }

        $processed = [];
        foreach ($rawLinks as $link) {
            $link = trim((string)$link);
            if (empty($link)) {
                continue;
            }

            // Ensure protocol
            if (!preg_match('/^https?:\/\//i', $link)) {
                $url = 'https://' . $link;
            } else {
                $url = $link;
            }

            $youtubeId = self::extractYouTubeId($url);
            $parsedHost = parse_url($url, PHP_URL_HOST) ?? $url;
            $domain = preg_replace('/^www\./i', '', $parsedHost);

            $processed[] = [
                'url' => $url,
                'youtube_id' => $youtubeId,
                'embed_url' => $youtubeId ? "https://www.youtube.com/embed/{$youtubeId}" : null,
                'domain' => $domain,
            ];
        }

        return $processed;
    }
}
