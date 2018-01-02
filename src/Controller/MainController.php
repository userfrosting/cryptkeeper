<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;

/**
 * Controller class for /* URLs.
 * Overwrites default behavior defined in the core sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class MainController extends SimpleController
{
    /**
     * Renders the default home page for UserFrosting.
     *
     * Redirect the user to the dashboard page
     * Request type: GET
     * @param  Request $request
     * @param  Response $response
     * @param  array $args
     */
    public function pageIndex(Request $request, Response $response, $args)
    {
        $dashboardPage = $this->ci->router->pathFor('dashboard');
        return $response->withRedirect($dashboardPage);
    }
}
