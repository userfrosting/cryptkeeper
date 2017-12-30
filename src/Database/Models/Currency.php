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
 * Represents a tradeable currency.
 */
class Currency extends Model
{
    public $timestamps = true;

    /**
     * The database table used by the model.
     * 
     * @var string
     */
    protected $table = 'currencies';

    protected $fillable = [
        'name',
        'name_cmc',
        'symbol'
    ];
}
