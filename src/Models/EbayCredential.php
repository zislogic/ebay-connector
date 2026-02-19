<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property string|null $name
 * @property string $environment
 * @property string $ebay_user_id
 * @property string|null $refresh_token
 * @property \Illuminate\Support\Carbon|null $refresh_token_expires_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder<static> active()
 * @method static Builder<static> environment(string $environment)
 */
final class EbayCredential extends Model
{
    protected $fillable = [
        'name',
        'environment',
        'ebay_user_id',
        'refresh_token',
        'refresh_token_expires_at',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'refresh_token_expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function getRefreshTokenAttribute(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::decryptString($value);
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = $value !== null ? Crypt::encryptString($value) : null;
    }

    /**
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeEnvironment(Builder $query, string $environment): Builder
    {
        return $query->where('environment', $environment);
    }

    public function deactivate(): bool
    {
        $this->is_active = false;

        return $this->save();
    }

    public function isRefreshTokenExpired(): bool
    {
        return $this->refresh_token_expires_at !== null
            && $this->refresh_token_expires_at->isPast();
    }
}
