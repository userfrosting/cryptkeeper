<?php 
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Represents a user's current holdings of a specific currency.
 */
class Holding extends Model
{
    public $timestamps = true;

    /**
     * The database table used by the model.
     * 
     * @var string
     */
    protected $table = 'holdings';

    protected $fillable = [
        'user_id',
        'currency_id',
        'balance',
        'note'
    ];

    /**
     * The user who owns this holding.
     */
    public function user()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Account\Database\Models\User', 'user_id');
    }

    /**
     * The currency this holding consists of.
     */
    public function currency()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Currency', 'currency_id');
    }

    /**
     * Joins the currency for this holding, so we can do things like sort, search, paginate, etc.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinCurrency($query)
    {
        $query->select('holdings.*');
        $query->addSelect(
            'currencies.name as name',
            'currencies.symbol as symbol'
        );

        $query->leftJoin('currencies', 'currencies.id', '=', 'holdings.currency_id');

        return $query;
    }
}
