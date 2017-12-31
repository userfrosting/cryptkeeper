<?php

namespace UserFrosting\Sprinkle\Cryptkeeper\Database\Models;

use UserFrosting\Sprinkle\Core\Database\Models\Model;

class MemberAux extends Model
{
    public $timestamps = false;

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'members';

    protected $fillable = [
        'fiat_currency_id'
    ];

    /**
     * Required to be able to access the `fiat_currency` relationship in Twig without needing to do eager loading.
     * @see http://stackoverflow.com/questions/29514081/cannot-access-eloquent-attributes-on-twig/35908957#35908957
     */
    public function __isset($name)
    {
        if (in_array($name, [
            'fiatCurrency'
        ])) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * Get the member's fiat currency.
     */
    public function fiatCurrency()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Currency', 'fiat_currency_id');
    }
}
