<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Database\Migrations\v100;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Currency;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Market;
use UserFrosting\System\Bakery\Migration;

/**
 * Markets table migration
 * Version 1.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class MarketsTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [
        '\UserFrosting\Sprinkle\Cryptkeeper\Database\Migrations\v100\CurrenciesTable'
    ];

    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('markets')) {
            $this->schema->create('markets', function (Blueprint $table) {
                $table->increments('id');
                // Primary currency, the units of which gains/losses on the exchange are measured in
                $table->integer('primary_currency_id')->unsigned();
                // Secondary currency, the currency that is bought/sold on this market using the primary currency
                $table->integer('secondary_currency_id')->unsigned();
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                $table->foreign('primary_currency_id')->references('id')->on('currencies');
                $table->foreign('secondary_currency_id')->references('id')->on('currencies');
            });
        }
    }

    /**
     * {@inheritDoc}
     */
    public function seed()
    {
        $usd = Currency::where('symbol', 'USD')->first();
        $gbp = Currency::where('symbol', 'GBP')->first();
        $btc = Currency::where('symbol', 'BTC')->first();
        $eth = Currency::where('symbol', 'ETH')->first();
        $ltc = Currency::where('symbol', 'LTC')->first();

        $markets = [
            'usd-btc' => new Market([
                'primary_currency_id' => $usd->id,
                'secondary_currency_id' => $btc->id
            ]),
            'gbp-btc' => new Market([
                'primary_currency_id' => $gbp->id,
                'secondary_currency_id' => $btc->id
            ]),
            'btc-eth' => new Market([
                'primary_currency_id' => $btc->id,
                'secondary_currency_id' => $eth->id
            ]),
            'btc-ltc' => new Market([
                'primary_currency_id' => $btc->id,
                'secondary_currency_id' => $ltc->id
            ])
        ];

        foreach ($markets as $id => $market) {
            $market->save();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('markets');
    }
}
