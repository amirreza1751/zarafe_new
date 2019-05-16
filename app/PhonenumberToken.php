<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhonenumberToken extends Model
{
    protected $table = 'phonenumber_tokens';

    protected $fillable = [
        'phone_number',
        'token',
        'used'
    ];
}
