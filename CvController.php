<?php

namespace App\Http\Controllers;

use DB;
use DateTime;
use App\Cv;
use App\CvImage;
use App\CvSkill;
use App\CvJob;
use App\CvSchool;
use App\CvInterest;
use App\CvQuote;
use App\CvReservation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Jcf\Geocode\Geocode;

// Contains "Resource Controller" calls...
// See: https://laravel.com/docs/5.6/controllers#resource-controllers
class CvController extends Controller
{
    
    
    public function FreeSearch(Request $request) {
        
        $city = $request->city;
        $keyWord=$request->search;
       
    

        function keywordList($value){
        $keyWord=$value;
        $index1 = Cv::search($keyWord)->raw(); 
        $list1= reset($index1);
        $index2 = CvSkill::search($keyWord)->raw(); 
        $list2= reset($index2);
        $cvList = DB::table('cv_skills') ->whereIn('id', $list2)->pluck('cv_id')->toArray();
        $CompleteList = array_merge($list1, $cvList);
        return $CompleteList;
        }

        function locationList($value){
            $city=$value;
            $miles = 50;
            $nearCity=[];
            $response = Geocode::make()->address($city);
    		  if ($response) {

                 $longitude=$response->longitude();
                 $latitude=$response->latitude();
            }

            $records =Cv::select('id')->whereRaw
            ("latitude between($latitude - ($miles*0.018)) and ($latitude + ($miles*0.018))")
            ->whereRaw("longitude between($longitude - ($miles*0.018))
             and ($longitude + ($miles*0.018))")->get();
        
            foreach ($records as $record){
            $nearCity[]=$record->id;
            }
            return $nearCity;
        }


        switch ([$city,$keyWord]) {
            case [NULL,!NULL]:
            $records = DB::table('cvs') ->whereIn('id', keywordList($keyWord))->get();
            return view('cvs/search/results', ["results" => $records]);
               
                break;

            case [!NULL,NULL]:
            $records = DB::table('cvs') ->whereIn('id', locationList($city))->get();
            return view('cvs/search/results', ["results" => $records]);

                break;

            case [NULL,NULL]:
                $records=Cv::All()->take(4);
                return view('cvs/search/results', ["results" => $records]);
                break;

            default:
            $includePhrase=keywordList($keyWord);
            $nearCity=locationList($city);
            $intersection=array_intersect($includePhrase,$nearCity);
            $records = DB::table('cvs') ->whereIn('id',$intersection)->get();
            return view('cvs/search/results', ["results" => $records]);
        }
        
            

        
       
    }
}
