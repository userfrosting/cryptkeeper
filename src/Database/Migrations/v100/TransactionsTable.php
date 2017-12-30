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
use UserFrosting\System\Bakery\Migration;

/**
 * Transactions table migration
 * Version 1.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class TransactionsTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable',
        '\UserFrosting\Sprinkle\Cryptkeeper\Database\Migrations\v100\MarketsTable',
        '\UserFrosting\Sprinkle\Cryptkeeper\Database\Migrations\v100\HoldingsTable'
    ];

    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('transactions')) {
            $this->schema->create('transactions', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('market_id', 50)->nullable();
                // Holding that you are selling
                $table->integer('from_holding_id')->unsigned();
                // Holding that you are buying
                $table->integer('to_holding_id')->unsigned();
                // Quantity of secondary currency bought/sold (e.g. BTC, XMR, ...)
                $table->float('quantity');
                // Exchange rate of primary currency to secondary currency (e.g., USD/BTC, BTC/XMR, ...)
                $table->float('price');
                // Transaction fee (in primary currency)
                $table->float('fee');
                $table->text('note');
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('market_id')->references('id')->on('markets');
                $table->foreign('from_holding_id')->references('id')->on('holdings');
                $table->foreign('to_holding_id')->references('id')->on('holdings');
            });
        }
    }

    /**
     * {@inheritDoc}
     */
    public function seed()
    {

    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('transactions');
    }
}
