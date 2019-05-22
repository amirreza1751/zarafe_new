<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $table = 'results';

    protected $fillable = [
        'user_id',
        'competition_id',
        'question_id',
        'answer'
    ];

    public function question()
    {
        return $this->belongsTo('App\Question', 'question_id');
    }
}
