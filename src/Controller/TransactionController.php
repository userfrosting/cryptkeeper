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
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Market;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Transaction;
use UserFrosting\Sprinkle\Cryptkeeper\Sprunje\TransactionSprunje;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\HttpException;

/**
 * Controller class for buy/sell-related requests.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class TransactionController extends SimpleController
{
    /**
     * Processes the request to create a new transaction.
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
        if (!$authorizer->checkAccess($currentUser, 'create_transaction')) {
            throw new ForbiddenException();
        }

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        $schema = new RequestSchema('schema://requests/transaction/create.yaml');

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

        // Get the market, make sure it exists
        $market = Market::find($data['market_id']);
        if (!$market) {
            $ms->addMessageTranslated('danger', 'Bad market id specified!');
            $error = true;
        }

        if ($error) {
            return $response->withStatus(400);
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var Config $config */
        $config = $this->ci->config;

        // All checks passed!  log events/activities and create transaction
        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction( function() use ($classMapper, $data, $ms, $config, $currentUser, $market) {
            // Get the holding for the current user and primary currency of this market
            $primaryHolding = Holding::where('user_id', $currentUser->id)
                ->where('currency_id', $market->primary_currency_id)
                ->first();

            // Create a new holding, if necessary
            if (!$primaryHolding) {
                $primaryHolding = new Holding([
                    'user_id' => $currentUser->id,
                    'currency_id' => $market->primary_currency_id
                ]);
                $primaryHolding->save();
            }

            // Get the holding for the current user and secondary currency of this market
            $secondaryHolding = Holding::where('user_id', $currentUser->id)
                ->where('currency_id', $market->secondary_currency_id)
                ->first();

            // Create a new holding, if necessary
            if (!$secondaryHolding) {
                $secondaryHolding = new Holding([
                    'user_id' => $currentUser->id,
                    'currency_id' => $market->secondary_currency_id
                ]);
                $secondaryHolding->save();
            }

            $data['user_id'] = $currentUser->id;
            $data['primary_holding_id'] = $primaryHolding->id;
            $data['secondary_holding_id'] = $secondaryHolding->id;

            // Transform date
            $data['completed_at'] = Carbon::createFromFormat($config['site.formats.datetime'], $data['completed_at']);

            // Create the transaction
            $transaction = new Transaction($data);

            // Store new transaction to database
            $transaction->save();

            // Create activity record
            $this->ci->userActivityLogger->info("User {$currentUser->user_name} bought/sold {$transaction->quantity} of {$secondaryHolding->currency->symbol}.", [
                'type' => 'transaction_create',
                'user_id' => $currentUser->id
            ]);

            $ms->addMessageTranslated('success', 'Successfully bought/sold {{quantity}} at {{price}}', $data);
        });

        return $response->withStatus(200);
    }

    /**
     * Returns a list of Transactions
     *
     * Generates a list of transactions, optionally paginated, sorted and/or filtered.
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
        if (!$authorizer->checkAccess($currentUser, 'uri_transactions')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = new TransactionSprunje($classMapper, $params);
        $sprunje->forMember($currentUser);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Renders the modal form for creating a new transaction.
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
        if (!$authorizer->checkAccess($currentUser, 'create_transaction')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Create a dummy object to prepopulate fields
        $transaction = new Transaction();

        // Load validation rules
        $schema = new RequestSchema('schema://requests/transaction/create.yaml');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'modals/transaction.html.twig', [
            'transaction' => $transaction,
            'markets' => Market::get(),
            'form' => [
                'action' => 'api/transactions',
                'method' => 'POST',
                'submit_text' => 'Create'
            ],
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Renders the transactions listing page.
     *
     * This page renders a table of transactions.
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
        if (!$authorizer->checkAccess($currentUser, 'uri_transactions')) {
            throw new ForbiddenException();
        }

        return $this->ci->view->render($response, 'pages/transactions.html.twig');
    }
}
