<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\User;
use App\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderNotify
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderCreated  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        $admin_ids=User::where('type','admin')->pluck('id');
        foreach ($admin_ids as $admin) {
            $notification=new Notification;
            $notification->user_id=$admin;
            $notification->read=FALSE;
            $notification->content='There is a new Order awaiting ';
            $notification->save();
            }

        

    }
}
