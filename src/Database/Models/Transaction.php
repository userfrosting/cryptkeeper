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
trait TransactionUpdateHoldingBalances
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function bootTransactionUpdateHoldingBalances()
    {
        /**
         * Update related holding balances when the transaction is created.
         */
        static::created(function ($transaction)
        {
            if ($transaction->primary_holding_id) {
                $transaction->primaryHolding->balance -= $transaction->quantity*$transaction->price;
                $transaction->primaryHolding->balance -= $transaction->fee;
                $transaction->primaryHolding->save();
            }

            if ($transaction->secondary_holding_id) {
                $transaction->secondaryHolding->balance += $transaction->quantity;
                $transaction->secondaryHolding->save();
            }
        });
    }
}

/**
 * Represents a transfer of funds from one account to another.
 */
class Transaction extends Model
{
    use TransactionUpdateHoldingBalances;

    public $timestamps = true;

    /**
     * The database table used by the model.
     * 
     * @var string
     */
    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'market_id',
        'primary_holding_id',
        'secondary_holding_id',
        'quantity',
        'price',
        'fee',
        'note',
        'completed_at'
    ];

    /**
     * The user involved in this transaction.
     */
    public function user()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Account\Database\Models\User', 'user_id');
    }

    /**
     * The market on which this transaction occurred.
     */
    public function market()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Market', 'market_id');
    }

    /**
     * The holding that was used to make the purchase or that was funded by the sale.
     */
    public function primaryHolding()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Holding', 'primary_holding_id');
    }

    /**
     * The holding that was bought/sold to/from.
     */
    public function secondaryHolding()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Holding', 'secondary_holding_id');
    }

    /**
     * Joins the market and its currencies for this transfer, so we can do things like sort, search, paginate, etc.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinMarket(Builder $query)
    {
        $query->select('transactions.*');
        $query->addSelect(
            'primary_currency.symbol as primary_currency_symbol',
            'secondary_currency.symbol as secondary_currency_symbol',
            Capsule::raw("(quantity * price) as gross_amount")
        );

        $query->leftJoin('markets', 'markets.id', '=', 'transactions.market_id');

        $query->leftJoin('currencies as primary_currency', 'primary_currency.id', '=', 'markets.primary_currency_id');
        $query->leftJoin('currencies as secondary_currency', 'secondary_currency.id', '=', 'markets.secondary_currency_id');

        return $query;
    }
}
