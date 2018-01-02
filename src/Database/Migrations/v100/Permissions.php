<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Database\Migrations\v100;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\System\Bakery\Migration;

/**
 * Permissions migration
 * Version 1.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Permissions extends Migration
{
    /**
     * @var array
     */
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable'
    ];

    /**
     * {@inheritDoc}
     */
    public function up()
    {
        // Get user role. If it doesn't exist, set to null
        $roleUser = (Role::where('slug', 'user')->first()) ?: null;

        foreach ($this->getPermissions() as $permission) {
            $permission = new Permission($permission);
            $permission->save();
            $permission->roles()->attach($roleUser);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        foreach ($this->getPermissions() as $permission) {
            $permission = Permission::where($permission)->first();
            if ($permission) {
                $permission->delete();
            }
        }
    }

    /**
     *    Returns the list of this sprinkles permissions
     *    @return array
     */
    protected function getPermissions()
    {
        return [
            [
                'slug' => 'uri_holdings',
                'name' => 'Holding page',
                'conditions' => 'always()',
                'description' => 'View the page with the list of your holdings.'
            ],
            [
                'slug' => 'uri_transfers',
                'name' => 'Transfers page',
                'conditions' => 'always()',
                'description' => 'View the page with the list of your transfers.'
            ],
            [
                'slug' => 'uri_transactions',
                'name' => 'Transactions page',
                'conditions' => 'always()',
                'description' => 'View the page with the list of your transactions.'
            ],
            [
                'slug' => 'uri_currencies',
                'name' => 'Currencies page',
                'conditions' => 'always()',
                'description' => 'View the currencies page.'
            ],
            [
                'slug' => 'uri_markets',
                'name' => 'Markets pages',
                'conditions' => 'always()',
                'description' => 'View the markets page.'
            ],
            [
                'slug' => 'create_transaction',
                'name' => 'Create transaction',
                'conditions' => 'always()',
                'description' => 'Add a transaction to your account.'
            ],
            [
                'slug' => 'create_transfer',
                'name' => 'Create Transfer',
                'conditions' => 'always()',
                'description' => 'Add a transfer to your account.'
            ]
        ];
    }
}
