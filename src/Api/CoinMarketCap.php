<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Api;

use GuzzleHttp\Client as Guzzle;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * CoinMarketCap
 *
 * Implements a wrapper for the Coin Market Cap API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CoinMarketCap
{
    protected $tickerUrl = 'https://api.coinmarketcap.com/v1/ticker';

    protected $quotes = [];

    public function __construct($convert = '')
    {
        // Retrieve coinmarketcap quotes and map by CMC id
        $client = new Guzzle;
        $response = $client->request('GET', $this->tickerUrl, [
            'query' => [
                'convert' => $convert
            ]
        ]);
        $quotesRaw = collect(json_decode($response->getBody(), true));

        $this->quotes = $quotesRaw->mapWithKeys(function ($item) {
            return [$item['id'] => $item];
        });
    }

    public function getQuotes()
    {
        return $this->quotes;
    }
}
