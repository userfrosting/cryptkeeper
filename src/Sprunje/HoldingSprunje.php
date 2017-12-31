<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Sprunje;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Holding;

/**
 * HoldingSprunje
 *
 * Implements Sprunje for the holdings API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class HoldingSprunje extends Sprunje
{
    protected $name = 'holdings';

    protected $sortable = [
        'name',
        'symbol',
        'balance',
        'note',
        'price_fiat',
        'price_btc',
        'value_fiat',
        'value_btc',
        'created_at'
    ];

    protected $filterable = [
        'name',
        'symbol',
        'balance',
        'note',
        'price_fiat',
        'price_btc',
        'value_fiat',
        'value_btc',
        'created_at'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = (new Holding())->newQuery()
            ->joinCurrency();

        return $query;
    }

    public function forMember($member)
    {
        $this->query = $member->holdings()
            ->joinCurrency()
            ->joinPrices();

        return $this;
    }

    /**
     * Filter by currency name
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterName($query, $value)
    {
        $query->like('currencies.name', $value);
    }

    /**
     * Filter by currency symbol
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterSymbol($query, $value)
    {
        $query->like('currencies.symbol', $value);
    }

    /**
     * Sort on holding currency name
     *
     * @param Builder $query
     * @param mixed $direction
     * @return Builder
     */
    protected function sortName($query, $direction)
    {
        $query->orderBy('currencies.name', $direction);
    }

    /**
     * Sort on holding currency symbol
     *
     * @param Builder $query
     * @param mixed $direction
     * @return Builder
     */
    protected function sortSymbol($query, $direction)
    {
        $query->orderBy('currencies.symbol', $direction);
    }
}
