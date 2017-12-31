<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Sprunje;

use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Market;

/**
 * MarketSprunje
 *
 * Implements Sprunje for the markets API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class MarketSprunje extends Sprunje
{
    protected $name = 'markets';

    protected $sortable = [
        'name',
        'last_exchange_rate',
        'updated_at'
    ];

    protected $filterable = [
        'name',
        'last_exchange_rate',
        'updated_at'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = (new Market())->newQuery()
            ->joinCurrencies()
            ->with('primaryCurrency', 'secondaryCurrency');

        return $query;
    }

    /**
     * Filter by currency names
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterName($query, $value)
    {
        $query->like('currencies_primary.symbol', $value)
            ->orLike('currencies_secondary.symbol', $value);
    }

    /**
     * Filter by update time
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterUpdatedAt($query, $value)
    {
        $query->like('markets.updated_at', $value);
    }

    /**
     * Sort on currency names
     *
     * @param Builder $query
     * @param mixed $direction
     * @return Builder
     */
    protected function sortName($query, $direction)
    {
        $query->orderBy('currencies_primary_symbol', $direction)
            ->orderBy('currencies_secondary_symbol', $direction);
    }
}
