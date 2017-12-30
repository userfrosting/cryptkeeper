<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Sprunje;

use Illuminate\Database\Capsule\Manager as Capsule;
use GuzzleHttp\Client as Guzzle;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Currency;

/**
 * CurrencySprunje
 *
 * Implements Sprunje for the currencies API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CurrencySprunje extends Sprunje
{
    protected $name = 'currencies';

    protected $sortable = [
        'name',
        'name_cmc',
        'symbol',
        'created_at'
    ];

    protected $filterable = [
        'name',
        'name_cmc',
        'symbol',
        'created_at'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = (new Currency())->newQuery();

        return $query;
    }

    protected function applyTransformations($collection)
    {
        // Retrieve coinmarketcap quotes and map by CMC id
        $client = new Guzzle;
        $response = $client->request('GET', 'https://api.coinmarketcap.com/v1/ticker');
        $quotes = collect(json_decode($response->getBody(), true));

        $quotesKeyed = $quotes->mapWithKeys(function ($item) {
            return [$item['id'] => $item];
        });

        // Map fiat and BTC prices to currencies in database
        $collection->transform(function ($item, $key) use ($quotesKeyed) {
            $name = $item->name_cmc;
            $quote = isset($quotesKeyed[$name]) ? $quotesKeyed[$name] : null;
            if ($quote) {
                $item->price_usd = $quote['price_usd'];
                $item->price_btc = $quote['price_btc'];
            }

            return $item;
        });

        return $collection;
    }
}
