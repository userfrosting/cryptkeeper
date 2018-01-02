<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

// Overwrite the index `/` route
$app->get('/', 'UserFrosting\Sprinkle\Cryptkeeper\Controller\MainController:pageIndex')
    ->add('checkEnvironment')
    ->setName('index');
