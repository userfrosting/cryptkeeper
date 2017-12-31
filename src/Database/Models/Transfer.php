<?php 
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Events for updating holdings balances.
 */
trait TransferUpdateHoldingBalances
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function bootTransferUpdateHoldingBalances()
    {
        /**
         * Update related holding balance when the transfer is created.
         */
        static::created(function ($transfer)
        {
            if ($transfer->holding_id) {
                $transfer->holding->balance += $transfer->amount;
                $transfer->holding->save();
            }
        });
    }
}

/**
 * Represents a transfer in to or out of a holding.
 */
class Transfer extends Model
{
    use TransferUpdateHoldingBalances;

    public $timestamps = true;

    /**
     * The database table used by the model.
     * 
     * @var string
     */
    protected $table = 'transfers';

    protected $fillable = [
        'user_id',
        'holding_id',
        'amount',
        'note'
    ];

    /**
     * The user who performed this transfer.
     */
    public function user()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Account\Database\Models\User', 'user_id');
    }

    /**
     * The holding this transfer was performed upon.
     */
    public function holding()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Holding', 'holding_id');
    }

    /**
     * Joins the currency for this transfer, so we can do things like sort, search, paginate, etc.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinCurrency(Builder $query)
    {
        $query->select('transfers.*');
        $query->addSelect(
            'currencies.name as name',
            'currencies.symbol as symbol'
        );

        $query->leftJoin('holdings', 'holdings.id', '=', 'transfers.holding_id');
        $query->leftJoin('currencies', 'currencies.id', '=', 'holdings.currency_id');

        return $query;
    }
}
