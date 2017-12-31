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
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Transaction;

/**
 * TransactionSprunje
 *
 * Implements Sprunje for the transactions API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class TransactionSprunje extends Sprunje
{
    protected $name = 'transactions';

    protected $sortable = [
        'market',
        'quantity',
        'price',
        'fee',
        'note',
        'gross_amount',
        'completed_at'
    ];

    protected $filterable = [
        'market',
        'quantity',
        'price',
        'fee',
        'note',
        'completed_at'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = (new Transaction())->newQuery()
            ->joinMarket();

        return $query;
    }

    public function forMember($member)
    {
        $this->query = $member->transactions()
            ->joinMarket();

        return $this;
    }

    /**
     * Filter by market symbols
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterMarket($query, $value)
    {
        $query->like('primary_currency.symbol', $value)
            ->orLike('secondary_currency.symbol', $value);
    }

    /**
     * Sort on market symbols and price
     *
     * @param Builder $query
     * @param mixed $direction
     * @return Builder
     */
    protected function sortMarket($query, $direction)
    {
        $query->orderBy('primary_currency.symbol', $direction)
            ->orderBy('secondary_currency.symbol', $direction)
            ->orderBy('price', $direction);
    }
}
