<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by_id');
    }

    public function currentTenant(): ?Tenant
    {
        $tenantId = session('current_tenant_id');
        if ($tenantId) {
            $tenant = $this->tenants()->find($tenantId);
            if ($tenant) {
                return $tenant;
            }
        }

        $defaultTenant = $this->tenants()->first();
        if ($defaultTenant) {
            session(['current_tenant_id' => $defaultTenant->id]);
            return $defaultTenant;
        }

        return null;
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w)) {
                $initials .= mb_substr($w, 0, 1);
            }
            if (mb_strlen($initials) >= 2) {
                break;
            }
        }
        return mb_strtoupper($initials ?: 'U');
    }

    public function getAvatarDisplayAttribute(): string
    {
        if ($this->avatar) {
            return $this->avatar;
        }
        return "https://api.dicebear.com/7.x/bottts/svg?seed=" . urlencode($this->name);
    }
}
