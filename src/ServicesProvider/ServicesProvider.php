<?php

// In /app/sprinkles/site/src/ServicesProvider/ServicesProvider.php

namespace UserFrosting\Sprinkle\Cryptkeeper\ServicesProvider;

use GuzzleHttp\Client as Guzzle;
use UserFrosting\Sprinkle\Cryptkeeper\Api\CoinMarketCap;

class ServicesProvider
{
    /**
     * Register extended user fields services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        /**
         * Extend the 'classMapper' service to register model classes.
         *
         * Mappings added: Member
         */
        $container->extend('classMapper', function ($classMapper, $c) {
            $classMapper->setClassMapping('user', 'UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Member');
            return $classMapper;
        });

        /**
         * Exposes a CoinMarketCap instance.
         */
        $container['cmc'] = function ($c) {
            $guzzle = new Guzzle;
            $cmc = new CoinMarketCap($guzzle);
            return $cmc;
        };
    }
}
