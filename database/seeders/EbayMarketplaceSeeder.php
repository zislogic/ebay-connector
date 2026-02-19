<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Database\Seeders;

use Illuminate\Database\Seeder;
use Zislogic\Ebay\Connector\Models\EbayMarketplace;

final class EbayMarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $marketplaces = [
            ['site_id' => 0, 'marketplace_id' => 'EBAY_US', 'global_id' => 'EBAY-US', 'site_code' => 'US', 'name' => 'United States', 'currency' => 'USD', 'locale' => 'en_US'],
            ['site_id' => 2, 'marketplace_id' => 'EBAY_CA', 'global_id' => 'EBAY-ENCA', 'site_code' => 'CA', 'name' => 'Canada', 'currency' => 'CAD', 'locale' => 'en_CA'],
            ['site_id' => 3, 'marketplace_id' => 'EBAY_GB', 'global_id' => 'EBAY-GB', 'site_code' => 'GB', 'name' => 'United Kingdom', 'currency' => 'GBP', 'locale' => 'en_GB'],
            ['site_id' => 15, 'marketplace_id' => 'EBAY_AU', 'global_id' => 'EBAY-AU', 'site_code' => 'AU', 'name' => 'Australia', 'currency' => 'AUD', 'locale' => 'en_AU'],
            ['site_id' => 16, 'marketplace_id' => 'EBAY_AT', 'global_id' => 'EBAY-AT', 'site_code' => 'AT', 'name' => 'Austria', 'currency' => 'EUR', 'locale' => 'de_AT'],
            ['site_id' => 23, 'marketplace_id' => 'EBAY_BE_FR', 'global_id' => 'EBAY-FRBE', 'site_code' => 'BE_FR', 'name' => 'Belgium (French)', 'currency' => 'EUR', 'locale' => 'fr_BE'],
            ['site_id' => 71, 'marketplace_id' => 'EBAY_FR', 'global_id' => 'EBAY-FR', 'site_code' => 'FR', 'name' => 'France', 'currency' => 'EUR', 'locale' => 'fr_FR'],
            ['site_id' => 77, 'marketplace_id' => 'EBAY_DE', 'global_id' => 'EBAY-DE', 'site_code' => 'DE', 'name' => 'Germany', 'currency' => 'EUR', 'locale' => 'de_DE'],
            ['site_id' => 100, 'marketplace_id' => 'EBAY_MOTORS_US', 'global_id' => 'EBAY-MOTOR', 'site_code' => 'MOTORS_US', 'name' => 'eBay Motors', 'currency' => 'USD', 'locale' => 'en_US'],
            ['site_id' => 101, 'marketplace_id' => 'EBAY_IT', 'global_id' => 'EBAY-IT', 'site_code' => 'IT', 'name' => 'Italy', 'currency' => 'EUR', 'locale' => 'it_IT'],
            ['site_id' => 123, 'marketplace_id' => 'EBAY_BE_NL', 'global_id' => 'EBAY-NLBE', 'site_code' => 'BE_NL', 'name' => 'Belgium (Dutch)', 'currency' => 'EUR', 'locale' => 'nl_BE'],
            ['site_id' => 146, 'marketplace_id' => 'EBAY_NL', 'global_id' => 'EBAY-NL', 'site_code' => 'NL', 'name' => 'Netherlands', 'currency' => 'EUR', 'locale' => 'nl_NL'],
            ['site_id' => 186, 'marketplace_id' => 'EBAY_ES', 'global_id' => 'EBAY-ES', 'site_code' => 'ES', 'name' => 'Spain', 'currency' => 'EUR', 'locale' => 'es_ES'],
            ['site_id' => 193, 'marketplace_id' => 'EBAY_CH', 'global_id' => 'EBAY-CH', 'site_code' => 'CH', 'name' => 'Switzerland', 'currency' => 'CHF', 'locale' => 'de_CH'],
            ['site_id' => 201, 'marketplace_id' => 'EBAY_HK', 'global_id' => 'EBAY-HK', 'site_code' => 'HK', 'name' => 'Hong Kong', 'currency' => 'HKD', 'locale' => 'zh_HK'],
            ['site_id' => 205, 'marketplace_id' => 'EBAY_IE', 'global_id' => 'EBAY-IE', 'site_code' => 'IE', 'name' => 'Ireland', 'currency' => 'EUR', 'locale' => 'en_IE'],
            ['site_id' => 207, 'marketplace_id' => 'EBAY_MY', 'global_id' => 'EBAY-MY', 'site_code' => 'MY', 'name' => 'Malaysia', 'currency' => 'MYR', 'locale' => 'ms_MY'],
            ['site_id' => 210, 'marketplace_id' => 'EBAY_CA_FR', 'global_id' => 'EBAY-FRCA', 'site_code' => 'CA_FR', 'name' => 'Canada (French)', 'currency' => 'CAD', 'locale' => 'fr_CA'],
            ['site_id' => 211, 'marketplace_id' => 'EBAY_PH', 'global_id' => 'EBAY-PH', 'site_code' => 'PH', 'name' => 'Philippines', 'currency' => 'PHP', 'locale' => 'en_PH'],
            ['site_id' => 212, 'marketplace_id' => 'EBAY_PL', 'global_id' => 'EBAY-PL', 'site_code' => 'PL', 'name' => 'Poland', 'currency' => 'PLN', 'locale' => 'pl_PL'],
            ['site_id' => 216, 'marketplace_id' => 'EBAY_SG', 'global_id' => 'EBAY-SG', 'site_code' => 'SG', 'name' => 'Singapore', 'currency' => 'SGD', 'locale' => 'en_SG'],
        ];

        foreach ($marketplaces as $marketplace) {
            EbayMarketplace::query()->updateOrCreate(
                ['site_id' => $marketplace['site_id']],
                $marketplace,
            );
        }
    }
}
