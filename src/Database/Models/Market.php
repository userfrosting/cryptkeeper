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
 * Represents a currency exchange market (e.g. USD/BTC, BTC/XMR, etc)
 */
class Market extends Model
{
    public $timestamps = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'markets';

    protected $fillable = [
        'primary_currency_id',
        'secondary_currency_id'
    ];

    /**
     * The primary currency used to buy and sell on this exchange.
     */
    public function primaryCurrency()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Currency', 'primary_currency_id');
    }

    /**
     * The currency that is bought or sold on this exchange.
     */
    public function secondaryCurrency()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Currency', 'secondary_currency_id');
    }

    /**
     * Directly joins the related currencies, so we can do things like sort, search, paginate, etc.
     */
    public function scopeJoinCurrencies($query)
    {
        $query->select(
            'markets.*',
            'currencies_primary.symbol as currencies_primary_symbol',
            'currencies_secondary.symbol as currencies_secondary_symbol'
        );

        $query
            ->leftJoin('currencies as currencies_primary', 'markets.primary_currency_id', '=', 'currencies_primary.id')
            ->leftJoin('currencies as currencies_secondary', 'markets.secondary_currency_id', '=', 'currencies_secondary.id');

        return $query;
    }
}
