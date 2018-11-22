<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\FeedBackRequest;
use App\Order;
use App\Feedback;
use App\Cv;
use App\User;


class FeedBackController extends Controller
{


    public function feedbackform(Request $request)
    {
        if (! $request->hasValidSignature()) {
            return 'Link Expired';
        }
        else{

            $cv_id=$request->cv_id;
            $user_id=$request->user_id;
            $cv=Cv::where('id',$cv_id)->first();
            
            return view('feedbackform')->with(['cv'=>$cv,'client_id'=>$user_id]);
        }
    }
    public function sbtFeedBack(Request $request)
    {
        $feedBack= new Feedback;
        $feedBack->body=$request['body'];
        $feedBack->num_val=$request['num_val'];
        $feedBack->client_id=$request['client_id'];
        $feedBack->cv_id=$request['cv_id'];
        $feedBack->save();
        Return "FeedBack Recieved ";

        

    }
}
