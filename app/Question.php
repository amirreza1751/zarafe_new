<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public function question_time()
    {
        return $this->belongsTo('App\QuestionTime', 'level', 'level');
    }
}