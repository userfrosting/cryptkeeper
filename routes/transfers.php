<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

$app->group('/transfers', function () {
    $this->get('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransferController:pageList')
        ->setName('uri_transfers');
})->add('authGuard');

$app->group('/api/transfers', function () {
    $this->get('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransferController:getList');

    $this->post('', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransferController:create');
})->add('authGuard');

$app->group('/modals/transfers', function () {
    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransferController:getModalConfirmDelete');

    $this->get('/create', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransferController:getModalCreate');

    $this->get('/edit', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\TransferController:getModalEdit');
})->add('authGuard');
