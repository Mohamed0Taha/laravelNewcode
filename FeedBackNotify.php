<?php

namespace App\Listeners;

use App\Events\FeedBackGiven;
use App\Notification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FeedBackNotify
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
     * @param  FeedBackGiven  $event
     * @return void
     */
    public function handle(FeedBackGiven $event)
    {
        $owner_id =$event->feedback->cv->owner_id;
        $admin_ids=User::where('type','admin')->pluck('id');
         $notifible=$admin_ids->push($owner_id);
         
        foreach ($notifible as $member) {
            $notification=new Notification;
            $notification->user_id=$member;
            $notification->read=FALSE;
            $notification->content='A new feed back is given ';
            $notification->save();
        }
        var_dump($notifible); 
    }
}
