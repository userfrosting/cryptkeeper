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
    public function scopeJoinCurrency(Builder $query)
    {
        $query->select('holdings.*');
        $query->addSelect(
            'currencies.name as name',
            'currencies.symbol as symbol'
        );

        $query->leftJoin('currencies', 'currencies.id', '=', 'holdings.currency_id');

        return $query;
    }

    /**
     * Joins the current prices in fiat and BTC, so we can do things like sort, search, paginate, etc.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinPrices(Builder $query)
    {
        $bitcoin = Currency::where('symbol', 'BTC')->first();

        $rawFiat = 'coalesce(markets_fiat.last_exchange_rate, 1/nullif(markets_fiat_reverse.last_exchange_rate, 0))';
        $rawBtc = 'coalesce(markets_btc.last_exchange_rate, 1/nullif(markets_btc_reverse.last_exchange_rate, 0), 1)';

        $query->addSelect('holdings.*');
        $query->addSelect(
            Capsule::raw("$rawFiat as price_fiat"),
            Capsule::raw("($rawFiat * balance) as value_fiat"),
            Capsule::raw("$rawBtc as price_btc"),
            Capsule::raw("($rawBtc * balance) as value_btc")
        );

        $query->leftJoin('members', 'members.id', '=', 'holdings.user_id');
        // Join market for user's fiat currency
        $query->leftJoin('markets as markets_fiat', function ($join) {
            $join->on('markets_fiat.primary_currency_id', '=', 'members.fiat_currency_id')
                 ->on('markets_fiat.secondary_currency_id', '=', 'holdings.currency_id');
        });

        // Join reverse market for user's fiat currency
        $query->leftJoin('markets as markets_fiat_reverse', function ($join) {
            $join->on('markets_fiat_reverse.primary_currency_id', '=', 'holdings.currency_id')
                 ->on('markets_fiat_reverse.secondary_currency_id', '=', 'members.fiat_currency_id');
        });

        // Join market for bitcoin
        $query->leftJoin('markets as markets_btc', function ($join) use ($bitcoin) {
            $join->on('markets_btc.secondary_currency_id', '=', 'holdings.currency_id')
                ->where('markets_btc.primary_currency_id', '=', $bitcoin->id);
        });

        // Join reverse market for bitcoin
        $query->leftJoin('markets as markets_btc_reverse', function ($join) use ($bitcoin) {
            $join->on('markets_btc_reverse.primary_currency_id', '=', 'holdings.currency_id')
                ->where('markets_btc_reverse.secondary_currency_id', '=', $bitcoin->id);
        });

        return $query;
    }
}
