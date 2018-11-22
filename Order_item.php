<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order_item extends Model
{
    protected $fillable = [
        'cv_id', 'interview_date', 'interview_place','order_id'
    ];

    public function order()
    {
        return $this->belongsTo('App\Order','id');
    }
}
