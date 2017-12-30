<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

$app->group('/account', function () {

    $this->get('/settings', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\AccountController:pageSettings')
        ->add('authGuard');

    $this->post('/settings/profile', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\AccountController:profile')
        ->add('authGuard');
});
