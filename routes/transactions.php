<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

$app->group('/transactions', function () {
    $this->get('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransactionController:pageList')
        ->setName('uri_transactions');
})->add('authGuard');

$app->group('/api/transactions', function () {
    $this->get('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransactionController:getList');

    $this->post('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransactionController:create');
})->add('authGuard');

$app->group('/modals/transactions', function () {
    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransactionController:getModalConfirmDelete');

    $this->get('/create', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransactionController:getModalCreate');

    $this->get('/edit', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransactionController:getModalEdit');
})->add('authGuard');
