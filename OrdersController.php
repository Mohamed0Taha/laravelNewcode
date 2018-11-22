<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\FeedBackRequest;
use App\Bookmark;
use App\User;
use App\Order;
use App\Cv;
use App\Order_item;

class OrdersController extends Controller

{ public function newOrder(Request $request)
    {
        
        $id = Auth::id();
        $order =  new Order;
        $order->description =$request['description'];
        $order->client_id=$id;
        $order->stage='pending';
       /* $order->project_start=$request['project_start'];
        $order->project_end=$request['project_end'];*/
        $order->save();
        return ;
        

    }

    public function ongoingHire(Request $request)
    {
        $id=Auth::id();
        $list= Order::where('client_id',$id)
        ->where('stage','ongoing')->with('user')->get();
        return $list;
        
    } 
    public function pendingHire(Request $request){
 
        $id=Auth::id();
        $list= Order::where('client_id',$id)->where('stage', 'pending')
        ->with('user')->get();
        return $list;
    }

    public function hireHistory(Request $request)
    {
        $id=Auth::id();
        $list= Order::where('client_id',$id)
        ->where('stage','closed')->with('user')->get();
        return $list;
        
    } 

    public function pendingOrder(Request $request){
 
        $list= Order::where('stage','!=', 'closed')
        ->where('stage','pending')->with('user')->get();
        return $list;
    }

    public function ongoingOrder(Request $request){

        $list= Order::where('stage','ongoing')->with('user')->get();
        return $list;

    }

    public function orderHistory(Request $request)
    {
        $list= Order::where('stage','closed')->with('user')->get();
        return $list;
        
    } 
    
    public function orderDetails(Request $request)
    {
        $user = Auth::user();
        $id=$request->order_id;
        $order= Order::where('id',$id)->with('user')->with('order_item')->first();
        $list=$order->order_item->pluck('cv_id');
        $records = json_decode(Cv::whereIn('id', $list)->get(), true);
        return [$records,$order];
        
    } 

    public function appendCv(Request $request)
    {
      
            $order_item= new Order_item;
            $order_item->order_id=$request->order_id;
            $order_item->cv_id=$request->cv_id;
            $order_item->save();
            return'cva added to order';
    } 
    public function removeCv(Request $request)
    {
      
         $cv=Order_item::where('order_id',$request->order_id)->where('cv_id',$request->cv_id)->delete();
            
            
    } 
    public function confirmOrder(Request $request)
    {
      
         $id=$request->order_id;
         $order=Order::where('id',$id)->first();
         $order->stage='ongoing';
         $order->save();
            
    } 

    public function closeOrder(Request $request)
    {
      
         $id=$request->order_id;
         $order=Order::where('id',$id)->first();
         $order->stage='closed';
         $order->save();
            
    } 
    public function reqFeedBack(Request $request)
    {
        $order_id=$request->order_id;
        $cv_id=$request->cv_id;
        $client_id=Order::where('id',$order_id)->pluck('client_id');
        $client=User::where('id',$client_id)->first();
        $cv_id= Order_item::where('order_id', $order_id)->where('cv_id', $cv_id)->pluck('cv_id');
        $cv=CV::where('id',$cv_id)->first();

        event(new FeedBackRequest($client,$cv));
        return "FeedBack Request is Emailed to {{$client->email}},with cv:{{$cv}}";
    
       
        
            
    } 



   




    
}
