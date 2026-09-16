<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'token',
        'prefix',
        'scopes',
        'last_used_at',
        'last_used_ip',
        'usage_count',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'last_used_at' => 'datetime',
        'last_used_ip' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function createToken(string $name, array $scopes = ['read'], int $userId = null): array
    {
        $plainToken = Str::random(80);
        $token = hash('sha256', $plainToken);
        $prefix = substr($plainToken, 0, 12);

        $apiToken = static::create([
            'user_id' => $userId ?? auth()->id(),
            'name' => $name,
            'token' => $token,
            'prefix' => $prefix,
            'scopes' => $scopes,
            'is_active' => true,
        ]);

        return [
            'token' => $plainToken,
            'id' => $apiToken->id,
            'name' => $apiToken->name,
            'prefix' => $prefix,
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? []);
    }

    public function recordUsage(string $ip = null): void
    {
        $this->update([
            'last_used_at' => now(),
            'usage_count' => $this->usage_count + 1,
            'last_used_ip' => array_merge($this->last_used_ip ?? [], [$ip ? sha1($ip) : null]),
        ]);
    }

    public function getMaskedTokenAttribute(): string
    {
        return $this->prefix . str_repeat('*', 60);
    }
}
