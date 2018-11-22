<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Events\OrderCreated;

class Order extends Model
{
    protected $fillable = [
    'owner_id', 'client_id', 'description' 
    ];

     protected $dispatchesEvents = [
    'created' => OrderCreated::class,
    ];
    public function order_item() 
    {
        return $this->hasMany('App\Order_item','order_id');
    }
    public function user()
    {
        return $this->hasOne('App\User','id','client_id');
    }
}

