<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone_number',
        'profile_photo',
        'role',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Keep `name` synced when first_name changes.
     */
    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name'] = $value;
        $this->attributes['name'] = trim(($value ?? '') . ' ' . ($this->attributes['last_name'] ?? $this->last_name ?? '')) ?: ($this->attributes['name'] ?? $this->name ?? '');
    }

    /**
     * Keep `name` synced when last_name changes.
     */
    public function setLastNameAttribute($value): void
    {
        $this->attributes['last_name'] = $value;
        $this->attributes['name'] = trim(($this->attributes['first_name'] ?? $this->first_name ?? '') . ' ' . ($value ?? '')) ?: ($this->attributes['name'] ?? $this->name ?? '');
    }

    /**
     * Accessor for name to prefer concatenated first & last if present.
     */
    public function getNameAttribute($value): string
    {
        $first = $this->attributes['first_name'] ?? $this->first_name ?? '';
        $last = $this->attributes['last_name'] ?? $this->last_name ?? '';
        $composed = trim($first . ' ' . $last);
        return $composed !== '' ? $composed : (string) ($value ?? '');
    }

    // 🔹 Filament Access Control
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status && in_array($this->role, ['admin', 'user']);
    }

    // 🔹 Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    // 🔹 Filament avatar URL
    public function getFilamentAvatarUrl(): ?string
    {
        if (!empty($this->profile_photo)) {
            // Stored on public disk under profiles/
            return asset('storage/' . ltrim($this->profile_photo, '/'));
        }

        return null;
    }
}
