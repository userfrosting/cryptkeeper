<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper;

use Carbon\Carbon;
use RocketTheme\Toolbox\Event\Event;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Market;
use UserFrosting\System\Sprinkle\Sprinkle;

/**
 * Bootstrapper class for the 'cryptkeeper' sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Cryptkeeper extends Sprinkle
{
    /**
     * Update values from CoinMarketCap, if it's time.
     */
    public function onSprinklesRegisterServices()
    {
        $config = $this->ci->config;

        $lastUpdatedAt = new Carbon(Market::max('updated_at'));
        // Add seconds from the last update time to get the next time we should update
        $updateAt = $lastUpdatedAt->addSeconds($config['site.currencies.refresh_interval']);

        // If we're past the update deadline, we need to update
        if (Carbon::now()->gte($updateAt)) {
            $cmc = $this->ci->cmc;
            $cmc->load();
        }
    }
}
