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
}
