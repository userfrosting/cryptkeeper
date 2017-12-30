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
}
