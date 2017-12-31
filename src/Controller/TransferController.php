<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Controller;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Currency;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Holding;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Transfer;
use UserFrosting\Sprinkle\Cryptkeeper\Sprunje\TransferSprunje;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\HttpException;

/**
 * Controller class for deposit/withdrawal-related requests.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class TransferController extends SimpleController
{
    /**
     * Processes the request to create a new transfer.
     *
     * This route requires authentication.
     * Request type: POST
     * @see getModalCreate
     */
    public function create($request, $response, $args)
    {
        // Get POST parameters: currency_id, amount, note
        $params = $request->getParsedBody();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_transfer')) {
            throw new ForbiddenException();
        }

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://requests/transfer/create.yaml');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        $error = false;

        // Validate request data
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            $error = true;
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        if ($error) {
            return $response->withStatus(400);
        }

        /** @var Config $config */
        $config = $this->ci->config;

        // All checks passed!  log events/activities and create course
        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction( function() use ($classMapper, $data, $ms, $config, $currentUser) {
            // Get the holding for the current user and currency
            $holding = Holding::where('user_id', $currentUser->id)
                ->where('currency_id', $data['currency_id'])
                ->first();

            // Create a new holding, if necessary
            if (!$holding) {
                $holding = new Holding([
                    'user_id' => $currentUser->id,
                    'currency_id' => $data['currency_id']
                ]);
                $holding->save();
            }

            $data['user_id'] = $currentUser->id;
            $data['holding_id'] = $holding->id;

            // Create the transfer
            $transfer = new Transfer($data);

            // Store new course to database
            $transfer->save();

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} transferred {$transfer->amount}.", [
                'type' => 'transfer_create',
                'user_id' => $currentUser->id
            ]);

            $ms->addMessageTranslated('success', 'Successfully transferred {{amount}}', $data);
        });

        return $response->withStatus(200);
    }

    /**
     * Returns a list of Transfers
     *
     * Generates a list of transfers, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     * Request type: GET
     */
    public function getList($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_transfers')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = new TransferSprunje($classMapper, $params);
        $sprunje->forMember($currentUser);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Renders the modal form for creating a new transfer.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalCreate($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        $translator = $this->ci->translator;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_transfer')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Create a dummy object to prepopulate fields
        $transfer = new Transfer();

        // Load validation rules
        $schema = new RequestSchema('schema://requests/transfer/create.yaml');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'modals/transfer.html.twig', [
            'transfer' => $transfer,
            'currencies' => Currency::get(),
            'form' => [
                'action' => 'api/transfers',
                'method' => 'POST',
                'submit_text' => 'Create'
            ],
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the transfers listing page.
     *
     * This page renders a table of transfers.
     * This page requires authentication.
     * Request type: GET
     */
    public function pageList($request, $response, $args)
    {
        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_transfers')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'pages/transfers.html.twig');
    }
}
