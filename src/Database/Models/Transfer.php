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
 * Represents a transfer in to or out of a holding.
 */
class Transfer extends Model
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
        'holding_id',
        'amount',
        'note'
    ];

    /**
     * The user who performed this transfer.
     */
    public function user()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Account\Database\Models\User', 'user_id');
    }

    /**
     * The holding this transfer was performed upon.
     */
    public function holding()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Holding', 'holding_id');
    }
}
