<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

$app->group('/markets', function () {
    $this->get('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\MarketController:pageList')
        ->setName('uri_markets');
})->add('authGuard');

$app->group('/api/markets', function () {
    $this->get('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\MarketController:getList');

    $this->post('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\MarketController:create');
})->add('authGuard');
