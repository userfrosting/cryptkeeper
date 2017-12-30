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
use UserFrosting\System\Bakery\Migration;

/**
 * Currencies table migration
 * Version 1.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CurrenciesTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('currencies')) {
            $this->schema->create('currencies', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('symbol', 20);
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
        }
    }

    /**
     * {@inheritDoc}
     */
    public function seed()
    {
        $currencies = [
            'usd' => new Currency([
                'name' => 'US Dollar',
                'symbol' => 'USD'
            ]),
            'cad' => new Currency([
                'name' => 'Canadian Dollar',
                'symbol' => 'CAD'
            ]),
            'aud' => new Currency([
                'name' => 'Australian Dollar',
                'symbol' => 'AUD'
            ]),
            'gbp' => new Currency([
                'name' => 'British Pound',
                'symbol' => 'GBP'
            ]),
            'btc' => new Currency([
                'name' => 'Bitcoin',
                'symbol' => 'BTC'
            ]),
            'eth' => new Currency([
                'name' => 'Ethereum',
                'symbol' => 'ETH'
            ]),
            'xrp' => new Currency([
                'name' => 'Ripple',
                'symbol' => 'XRP'
            ]),
            'bcc' => new Currency([
                'name' => 'Bitcoin Cash',
                'symbol' => 'BCC'
            ]),
            'ada' => new Currency([
                'name' => 'Cardano',
                'symbol' => 'ADA'
            ]),
            'ltc' => new Currency([
                'name' => 'Litecoin',
                'symbol' => 'LTC'
            ]),
            'iota' => new Currency([
                'name' => 'Iota',
                'symbol' => 'MIOTA'
            ]),
            'nem' => new Currency([
                'name' => 'New Economy Movement',
                'symbol' => 'XEM'
            ]),
            'dash' => new Currency([
                'name' => 'Dash',
                'symbol' => 'DASH'
            ]),
            'xmr' => new Currency([
                'name' => 'Monero',
                'symbol' => 'XMR'
            ]),
            'xlm' => new Currency([
                'name' => 'Stellar Lumens',
                'symbol' => 'XLM'
            ]),
            'etn' => new Currency([
                'name' => 'Electroneum',
                'symbol' => 'ETN'
            ])
        ];

        foreach ($currencies as $id => $currency) {
            $currency->save();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('currencies');
    }
}
