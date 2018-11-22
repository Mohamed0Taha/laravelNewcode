<?php

namespace App\Listeners;
use Mail;

use App\Events\FeedBackRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\FeedBackMail;
use Illuminate\Support\Facades\Auth;
use App\User;

class FeedBackDispatcher
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
     * @param  FeedBackRequest  $event
     * @return void
     */
    public function handle(FeedBackRequest $event)
    {
    
        $user = $event->user;
        $cv=$event->cv;
        
        Mail::to($user->email)->send(new FeedBackMail($user,$cv));
    }
}
