<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'score',
        'user_id',
        'competition_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function getRanking(){
        $collection = collect(Score::orderBy('score', 'DESC')->distinct('score')->get(['score']));
        $data       = $collection->where('score', $this->score);
        $value      = $data->keys()->first() + 1;
        return $value;
    }
}
