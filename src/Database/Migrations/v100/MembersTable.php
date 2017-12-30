<?php
namespace UserFrosting\Sprinkle\Cryptkeeper\Database\Migrations\v100;

use UserFrosting\System\Bakery\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

class MembersTable extends Migration
{
    public $dependencies = [
        '\UserFrosting\Sprinkle\Cryptkeeper\Database\Migrations\v100\CurrenciesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable'
    ];

    public function up()
    {
        if (!$this->schema->hasTable('members')) {
            $this->schema->create('members', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('fiat_currency_id')->default(1)->unsigned();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                $table->foreign('fiat_currency_id')->references('id')->on('currencies');
            });
        }
    }

    public function down()
    {
        $this->schema->drop('members');
    }
}
