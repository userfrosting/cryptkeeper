<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

$app->group('/holdings', function () {
    $this->get('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\HoldingController:pageList')
        ->setName('uri_holdings');
})->add('authGuard');

$app->group('/api/holdings', function () {
    $this->get('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\HoldingController:getList');

    $this->post('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\HoldingController:create');
})->add('authGuard');
