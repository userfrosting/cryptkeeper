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
 * Transfers table migration
 * Version 1.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class TransfersTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable',
        '\UserFrosting\Sprinkle\Cryptkeeper\Database\Migrations\v100\HoldingsTable'
    ];

    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('transfers')) {
            $this->schema->create('transfers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('holding_id')->unsigned();
                $table->double('amount', 20, 10)->default(0);
                $table->text('note');
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('holding_id')->references('id')->on('holdings');
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
        $this->schema->drop('transfers');
    }
}
