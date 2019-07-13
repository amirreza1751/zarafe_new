<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = "questions";
    protected $fillable = [
        'text',
        'option1',
        'option2',
        'option3',
        'option4',
        'level',
        'link_hls',
        'link_dash',
        'link_dash',
        'correct_answer',
        'competition_id',
        'video_length'
    ];
    public function question_time()
    {
        return $this->belongsTo('App\QuestionTime', 'level', 'level');
    }
}