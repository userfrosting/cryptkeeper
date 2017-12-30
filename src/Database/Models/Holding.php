<?php 
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Cryptkeeper\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Represents a user's current holdings of a specific currency.
 */
class Holding extends Model
{
    public $timestamps = true;

    /**
     * The database table used by the model.
     * 
     * @var string
     */
    protected $table = 'holdings';

    protected $fillable = [
        'user_id',
        'currency_id',
        'balance'
    ];

    /**
     * The user who owns this holding.
     */
    public function user()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Account\Database\Models\User', 'user_id');
    }

    /**
     * The currency this holding consists of.
     */
    public function currency()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Currency', 'currency_id');
    }
}
