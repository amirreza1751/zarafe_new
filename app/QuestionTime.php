<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionTime extends Model
{
    public function questions()
    {
        return $this->hasMany('App\Question', 'level');
    }
}
