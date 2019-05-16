<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TonightQuestion extends Model
{
    protected $table = 'tonight_questions';

    protected $fillable = [
        'user_id',
        'question_id',
        'used',
        'time'
    ];
    public function question()
    {
        return $this->belongsTo('App\Question', 'question_id');
    }
}
