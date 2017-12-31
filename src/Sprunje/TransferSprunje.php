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
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Transfer;

/**
 * TransferSprunje
 *
 * Implements Sprunje for the transfers API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class TransferSprunje extends Sprunje
{
    protected $name = 'transfers';

    protected $sortable = [
        'name',
        'symbol',
        'amount',
        'note',
        'completed_at'
    ];

    protected $filterable = [
        'name',
        'symbol',
        'amount',
        'note',
        'completed_at'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = (new Transfer())->newQuery()
            ->joinCurrency();

        return $query;
    }

    public function forMember($member)
    {
        $this->query = $member->transfers()
            ->joinCurrency();

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
