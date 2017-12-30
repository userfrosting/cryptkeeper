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
            if ($transaction->from_holding_id) {
                $transaction->fromHolding->balance -= $transaction->amount;
                $transaction->fromHolding->save();
            }

            if ($transaction->to_holding_id) {
                $transaction->toHolding->balance += $transaction->amount;
                $transaction->toHolding->save();
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
        'from_holding_id',
        'to_holding_id',
        'quantity',
        'price',
        'fee',
        'note'
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
     * The holding that was transferred from.
     */
    public function fromHolding()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Holding', 'from_holding_id');
    }

    /**
     * The account that was transferred to.
     */
    public function toHolding()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Holding', 'to_holding_id');
    }
}
