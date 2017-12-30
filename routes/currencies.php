<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

$app->group('/currencies', function () {
    $this->get('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\CurrencyController:pageList')
        ->setName('uri_currencies');
})->add('authGuard');

$app->group('/api/currencies', function () {
    $this->get('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\CurrencyController:getList');

    $this->post('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\CurrencyController:create');
})->add('authGuard');
