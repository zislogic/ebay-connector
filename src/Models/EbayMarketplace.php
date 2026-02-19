<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $site_id
 * @property string $marketplace_id
 * @property string $global_id
 * @property string $site_code
 * @property string $name
 * @property string $currency
 * @property string $locale
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class EbayMarketplace extends Model
{
    protected $fillable = [
        'site_id',
        'marketplace_id',
        'global_id',
        'site_code',
        'name',
        'currency',
        'locale',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'site_id' => 'integer',
        ];
    }

    public static function findBySiteId(int $siteId): ?self
    {
        return static::query()->where('site_id', $siteId)->first();
    }

    public static function findByMarketplaceId(string $marketplaceId): ?self
    {
        return static::query()->where('marketplace_id', $marketplaceId)->first();
    }

    public static function findBySiteCode(string $siteCode): ?self
    {
        return static::query()->where('site_code', $siteCode)->first();
    }
}
