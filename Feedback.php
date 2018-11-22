<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Events\FeedBackGiven;

class Feedback extends Model
{
    protected $dispatchesEvents = [
        'created' => FeedBackGiven::class,
    ];
    protected $fillable = [
        'cv_id', 'client_id', 'num_val','body'
    ];

    public function cv()
    {
        return $this->hasOne('App\Cv','id','cv_id');
    }
    
    public function user()
    {
        return $this->hasOne('App\User','id','client_id');
    }
}
