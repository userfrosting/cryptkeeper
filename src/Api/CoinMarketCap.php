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
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Currency;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Market;

/**
 * CoinMarketCap
 *
 * Implements a wrapper for the Coin Market Cap API, which can load quote data into our database.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CoinMarketCap
{
    /**
     * @var Guzzle
     */
    protected $guzzle;

    /**
     * @var string
     */
    protected $tickerUrl = 'https://api.coinmarketcap.com/v1/ticker';

    /**
     * @param Guzzle $guzzle
     */
    public function __construct(Guzzle $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * Reload quotes from the CoinMarketCap API into the database.
     */
    public function load()
    {
        // Map CMC names to currency ids and symbols
        $primaryCurrencies = $this->mapCurrencyNames(Currency::has('primaryMarkets')->get());

        // Now build a list of all currencies
        $currencies = $this->mapCurrencyNames(Currency::get());

        // We'll build a nested array of secondaryCurrencyId -> primaryCurrencyId -> rate
        $exchangeRates = [];

        $bitcoinId = $currencies['bitcoin']['id'];

        // For each of these currencies (besides bitcoin), we will fire a request to the CMC API.
        // On the first request, we're going to just get the default USD and BTC prices.
        foreach ($primaryCurrencies as $name => $properties) {
            $primaryId = $properties['id'];
            $primarySymbol = $properties['symbol'];
            $priceAttribute = 'price_' . $primarySymbol;

            // Retrieve quotes for the given primary currency
            $quotes = $this->retrieveQuotes($name);
            foreach ($quotes as $quote) {
                $secondaryName = $quote['id'];
                $secondaryId = $currencies[$secondaryName]['id'];

                // Skip any quotes that don't correspond to currencies in our dictionary
                if (!isset($secondaryId)) {
                    continue;
                }

                // Set up outer dictionary entry if not already set
                if (!isset($exchangeRates[$secondaryId])) {
                    $exchangeRates[$secondaryId] = [];
                }

                // Set the bitcoin rate, if not already set
                if (!isset($exchangeRates[$secondaryId][$bitcoinId])) {
                    $exchangeRates[$secondaryId][$bitcoinId] = $quote['price_btc'];
                }

                // Set primary rate
                $exchangeRates[$secondaryId][$primaryId] = $quote[$priceAttribute];
            }
        }

        // Get all of our markets, and update their last_exchange_rate using our dictionary
        $markets = Market::all();
        foreach ($markets as $market) {
            $newRate = $exchangeRates[$market->secondary_currency_id][$market->primary_currency_id];
            if (isset($newRate)) {
                $market->last_exchange_rate = $newRate;
                $market->save();
            }
        }
    }

    /**
     * Map currency names, by their name_cmc column, to their DB id and symbol.
     *
     * @param Collection $models
     * @return Collection
     */
    protected function mapCurrencyNames($models)
    {
        // Map CMC names to currency ids and symbols
        return $models->mapWithKeys(function ($item) {
            return [
                $item['name_cmc'] => [
                    'id' => $item['id'],
                    'symbol' => str_slug($item['symbol'])
                ]
            ];
        });
    }

    /**
     * Retrieve CMC quotes, optionally specifying a non-USD fiat currency
     *
     * @param string $convert
     * @return array
     */
    protected function retrieveQuotes($convert = '')
    {
        $params = [];
        if (!empty($convert)) {
            $params['query'] = [
                'convert' => $convert
            ];
        }

        $response = $this->guzzle->request('GET', $this->tickerUrl, $params);
        $quotesRaw = json_decode($response->getBody(), true);

        return $quotesRaw;
    }
}
