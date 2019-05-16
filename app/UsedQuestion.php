<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsedQuestion extends Model
{
    protected $table = 'used_questions';

    protected $fillable = [
        'user_id',
        'question_id',
    ];
}
