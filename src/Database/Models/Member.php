<?php
namespace UserFrosting\Sprinkle\Cryptkeeper\Database\Models;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Models\MemberAux;
use UserFrosting\Sprinkle\Cryptkeeper\Database\Scopes\MemberAuxScope;

trait LinkMemberAux
{
    /**
     * The "booting" method of the trait.
     *
     * @return void
     */
    protected static function bootLinkMemberAux()
    {
        /**
         * Create a new MemberAux if necessary, and save the associated member data every time.
         */
        static::saved(function ($member) {
            $member->createAuxIfNotExists();

            if ($member->auxType) {
                // Set the aux PK, if it hasn't been set yet
                if (!$member->aux->id) {
                    $member->aux->id = $member->id;
                }

                $member->aux->save();
            }
        });
    }
}

class Member extends User
{
    use LinkMemberAux;

    protected $fillable = [
        'user_name',
        'first_name',
        'last_name',
        'email',
        'locale',
        'theme',
        'group_id',
        'flag_verified',
        'flag_enabled',
        'last_activity_id',
        'password',
        'deleted_at',
        'fiat_currency_id'
    ];

    protected $auxType = 'UserFrosting\Sprinkle\Cryptkeeper\Database\Models\MemberAux';

    /**
     * Required to be able to access the `aux` relationship in Twig without needing to do eager loading.
     * @see http://stackoverflow.com/questions/29514081/cannot-access-eloquent-attributes-on-twig/35908957#35908957
     */
    public function __isset($name)
    {
        if (in_array($name, [
            'aux'
        ])) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * Globally joins the `members` table to access additional properties.
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new MemberAuxScope);
    }

    /**
     * Get the member's holdings.
     */
    public function holdings()
    {
        return $this->hasMany('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Holding', 'user_id');
    }

    /**
     * Get the member's transfers.
     */
    public function transfers()
    {
        return $this->hasMany('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Transfer', 'user_id');
    }

    /**
     * Get the member's transactions.
     */
    public function transactions()
    {
        return $this->hasMany('UserFrosting\Sprinkle\Cryptkeeper\Database\Models\Transaction', 'user_id');
    }

    /**
     * Custom mutator for Member property
     */
    public function setFiatCurrencyIdAttribute($value)
    {
        $this->createAuxIfNotExists();

        $this->aux->fiat_currency_id = $value;
    }

    /**
     * Relationship for interacting with aux model (`members` table).
     */
    public function aux()
    {
        return $this->hasOne($this->auxType, 'id');
    }

    /**
     * If this instance doesn't already have a related aux model (either in the db on in the current object), then create one
     */
    protected function createAuxIfNotExists()
    {
        $config = static::$ci->config;

        if ($this->auxType && !count($this->aux)) {
            // Create aux model and set default fiat currency id
            $aux = new $this->auxType;
            $aux->fiat_currency_id = $config['site.currencies.fiat_default_id'];

            // Needed to immediately hydrate the relation.  It will actually get saved in the bootLinkMemberAux method.
            $this->setRelation('aux', $aux);
        }
    }
}
