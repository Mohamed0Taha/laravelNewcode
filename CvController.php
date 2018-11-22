<?php

namespace App\Http\Controllers\Api;

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

class CvController extends Controller
{

    public function cities()
    {
        $cities = Config::get('constants.cities.fin');
        return $cities;
    
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $general = $request->general;
        
        $cv = Cv::create([
            'owner_id' => $user->id,
            'summary' => $general['summary'],
            'name' => $general['name'],
            'title' => $general['title'],
            'city' => $general['city'],
            'country' => $general['country'],
            'date_of_birth_month' => $general['date_of_birth_month'],
            'date_of_birth_year' => $general['date_of_birth_year'],
            'career_started_month' => $general['career_started_month'],
            'career_started_year' => $general['career_started_year'],
        ]);
    
        if ($request->cropped_img) {
            $user_id = $user->id;
            $cv_id = $cv->id;
            
            $image = $request->cropped_img;
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $path = public_path('data/' .$user_id. '/images/' .$cv_id);
            $path_name = 'data/' .$user_id. '/images/' .$cv_id. '/photo_md.jpg';
            \File::makeDirectory($path, $mode = 0777, true, true);
            \File::put($path_name, base64_decode($image)); 
            $cv->photo_md = $path_name;
        } else {
            $path = public_path('images/user.png');
            $path_name = 'images/user.png';
            $cv->photo_md = $path_name;
        }

        $cv->save();

        $i = 0;
        if ($request->skills[0]['name'] != null) {
            $skills = $request->skills;
            while ($i < count($skills)) {
                $skill = new CvSkill;
                $skill->cv_id = $cv->id;
                $skill->name = $skills[$i]['name'];
                $skill->level = $skills[$i]['level'];
                $skill->save();
                $i++;
            }
        }

        $i = 0;
        if ($request->experience[0]['title'] != null) {
            $jobs = $request->experience;
            while ($i < count($jobs)) {
                $job = new CvJob;
                $job->cv_id = $cv->id;
                $job->title = $jobs[$i]['title'];
                $job->company = $jobs[$i]['company'];
                $job->location = $jobs[$i]['location'];
                $job->time_from_month = $jobs[$i]['time_from_month'];
                $job->time_from_year = $jobs[$i]['time_from_year'];
                $job->time_to_month = $jobs[$i]['time_to_month'];
                $job->time_to_year = $jobs[$i]['time_to_year'];
                $job->current = $jobs[$i]['current'];
                $job->description = $jobs[$i]['description'];
                $job->save();
                $i++;
            }
        }
        $i = 0;
        if ($request->educations[0]['institution'] != null) {
            $schools = $request->educations;
            while ($i < count($schools)) {
                $school = new CvSchool;
                $school->cv_id = $cv->id;
                $school->institution = $schools[$i]['institution'];
                $school->time_from_month = $schools[$i]['time_from_month'];
                $school->time_from_year = $schools[$i]['time_from_year'];
                $school->time_to_month = $schools[$i]['time_to_month'];
                $school->time_to_year = $schools[$i]['time_to_year'];
                $school->save();
                $i++;
            }
        }

        $i = 0;
        if ($request->interests[0]['interest'] != null) {
            $interests = $request->interests;
            while ($i < count($interests)) {
                $interest = new CvInterest;
                $interest->cv_id = $cv->id;
                $interest->interest = $interests[$i]['interest'];
                $interest->save();
                $i++;
            }
        }

        $i = 0;
        if ($request->quotes[0]['quote'] != null) {
            $quotes = $request->quotes;
            while ($i < count($quotes)) {
                $quote = new CvQuote;
                $quote->cv_id = $cv->id;
                $quote->quote = $quotes[$i]['quote'];
                $quote->from = $quotes[$i]['from'];
                $quote->save();
                $i++;
            }
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Cv  $cv
     * @return \Illuminate\Http\Response
     */

    public function fetch(Request $request) {

        $user = $request->user();
        $cvs = DB::table('cvs')->where('owner_id', $user->id)->get();
        return response()->json(['cvs' => $cvs]);

    } 

    public function search(Request $request) {
        
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

        function recordsLoop($records) {
            $i = 0;
            while ($i < count($records)) {
                $this_id = $records[$i]['id'];
                $skills = DB::table('cv_skills')->where('cv_id', $this_id)->select('name', 'level')->get();
                $records[$i] = array('general' => $records[$i], 'skills' => $skills);
                $i++;
            }
            return $records;
        }

        switch ([$city,$keyWord]) {
            case [NULL,!NULL]:
                $records = json_decode(DB::table('cvs') ->whereIn('id', keywordList($keyWord))->get(), true);
                $records = recordsLoop($records);
                return response()->json(['results' => $records]);
            
                break;

            case [!NULL,NULL]:
                $records = json_decode(DB::table('cvs') ->whereIn('id', locationList($city))->get(), true);
                $records = recordsLoop($records);
                return response()->json(['results' => $records]);

                break;

            case [NULL,NULL]:
                $records=Cv::All()->take(4);
                $records = recordsLoop($records);
                return response()->json(['results' => $records]);
                
                break;

            default:
            $includePhrase=keywordList($keyWord);
            $nearCity=locationList($city);
            $intersection=array_intersect($includePhrase,$nearCity);

            $records = json_decode(DB::table('cvs')->whereIn('id', $intersection)->get(), true);
            $records = recordsLoop($records);
            return response()->json(['results' => $records]);
        }    
      
        
    }

    public function details(Request $request, $id)
    {
        $user = $request->user();
        
        $user_id = $request->user()->id;
        $img_path = DB::table('cvs')->where('id', $id)->first()->photo_md;

        $img = CvImage::getImage($user_id, $id, $img_path);

        $cv = DB::table('cvs')->where('id', $id)
            ->select('name', 'title', 'country', 'city', 'summary', 'date_of_birth_month', 'date_of_birth_year', 
            'career_started_month', 'career_started_year')->first();
        $skills = DB::table('cv_skills')->where('cv_id', $id)
            ->select('name', 'level')->get();
        $r = array('cropped_img' => $img, 'general' => $cv, 'skills' => $skills);

        return response()->json($r);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $user_id = $request->user()->id;
        $img_path = DB::table('cvs')->where('id', $id)->first()->photo_md;

        $img = CvImage::getImage($user_id, $id, $img_path);

        $cv = DB::table('cvs')->where('id', $id)
            ->select('name', 'title', 'country', 'city', 'summary', 'date_of_birth_month', 'date_of_birth_year', 
            'career_started_month', 'career_started_year')->first();
        $skills = DB::table('cv_skills')->where('cv_id', $id)
            ->select('name', 'level')->get();
        $jobs = DB::table('cv_jobs')->where('cv_id', $id)
            ->select('title', 'company', 'location', 'time_from_month', 'time_from_year', 
            'time_to_month', 'time_to_year', 'current', 'description')->get();
        $schools = DB::table('cv_schools')->where('cv_id', $id)
            ->select('institution', 'time_from_month', 'time_from_year', 'time_to_month', 'time_to_year')->get();
        $interests = DB::table('cv_interests')->where('cv_id', $id)
            ->select('interest')->get();
        $quotes = DB::table('cv_quotes')->where('cv_id', $id)
            ->select('quote', 'from')->get();

        $r = array('cropped_img' => $img, 'general' => $cv, 'skills' => $skills, 'experience' => $jobs, 'educations' => $schools, 'interests' => $interests, 'quotes' => $quotes);

        return response()->json($r);


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cv  $cv
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $user_id = $request->user()->id;
        $general = $request->general;

        $img_path = DB::table('cvs')->where('id', $id)->first()->photo_md;
        if ($request->cropped_img) {
            CvImage::changeImage($request, $user_id, $id, $img_path);
        }

        $cv = DB::table('cvs')->where('id', $id);
        
        $cv->update([
            'summary' => $general['summary'],
            'name' => $general['name'],
            'title' => $general['title'],
            'city' => $general['city'],
            'country' => $general['country'],
            'date_of_birth_month' => $general['date_of_birth_month'],
            'date_of_birth_year' => $general['date_of_birth_year'],
            'career_started_month' => $general['career_started_month'],
            'career_started_year' => $general['career_started_year'],
        ]);

        $i = 0;
        DB::table('cv_skills')->where('cv_id', $id)->delete();
        if ($request->skills !== null) {
            $skills = $request->skills;
            while ($i < count($skills)) {
                $skill = new CvSkill;
                $skill->cv_id = $id;
                $skill->name = $skills[$i]['name'];
                $skill->level = $skills[$i]['level'];
                $skill->save();
                $i++;
            }
        }

        $i = 0;
        DB::table('cv_jobs')->where('cv_id', $id)->delete();
        if ($request->experience !== null) {
            $jobs = $request->experience;
            while ($i < count($jobs)) {
                $job = new CvJob;
                $job->cv_id = $id;
                $job->title = $jobs[$i]['title'];
                $job->company = $jobs[$i]['company'];
                $job->location = $jobs[$i]['location'];
                $job->time_from_month = $jobs[$i]['time_from_month'];
                $job->time_from_year = $jobs[$i]['time_from_year'];
                $job->time_to_month = $jobs[$i]['time_to_month'];
                $job->time_to_year = $jobs[$i]['time_to_year'];
                $job->current = $jobs[$i]['current'];
                $job->description = $jobs[$i]['description'];
                $job->save();
                $i++;
            }
        }

        $i = 0;
        DB::table('cv_schools')->where('cv_id', $id)->delete();
        if ($request->educations != null) {
            $schools = $request->educations;
            while ($i < count($schools)) {
                $school = new CvSchool;
                $school->cv_id = $id;
                $school->institution = $schools[$i]['institution'];
                $school->time_from_month = $schools[$i]['time_from_month'];
                $school->time_from_year = $schools[$i]['time_from_year'];
                $school->time_to_month = $schools[$i]['time_to_month'];
                $school->time_to_year = $schools[$i]['time_to_year'];
                $school->save();
                $i++;
            }
        }

        $i = 0;
        DB::table('cv_interests')->where('cv_id', $id)->delete();
        if ($request->interests != null) {
            $interests = $request->interests;
            while ($i < count($interests)) {
                $interest = new CvInterest;
                $interest->cv_id = $id;
                $interest->interest = $interests[$i]['interest'];
                $interest->save();
                $i++;
            }
        }

        $i = 0;
        DB::table('cv_quotes')->where('cv_id', $id)->delete();
        if ($request->quotes != null) {
            $quotes = $request->quotes;
            while ($i < count($quotes)) {
                $quote = new CvQuote;
                $quote->cv_id = $id;
                $quote->quote = $quotes[$i]['quote'];
                $quote->from = $quotes[$i]['from'];
                $quote->save();
                $i++;
            }
        }
    }


    public function showReservations(Request $request, $id)
    {
        $reservations = DB::table('cv_reservations')->where('cv_id', $id)->orderBy('start_time')->get();
        return response()->json(['reservations' => $reservations]);
    }

    public function storeReservations(Request $request, $id)
    {

        DB::table('cv_reservations')->where('cv_id', $id)->delete();
        $i = 0;
        if ($request->reservations != null) {
            $reservations = $request->reservations;
            while ($i < count($reservations)) {
                $reservation = new CvReservation;
                $reservation->cv_id = $id;
                $reservation->project_name = $reservations[$i]['project_name'];

                $start_date = $reservations[$i]['start_time'];
                $end_date = $reservations[$i]['end_time'];

                if (!is_a($start_date, 'DateTime')) {
                    $reservation->start_time = new DateTime($start_date);
                } else {
                    $reservation->start_time = $start_date;
                }
                if (!is_a($end_date, 'DateTime')) {
                    $reservation->end_time = new DateTime($end_date);
                } else {
                    $reservation->end_time = $end_date;
                }

                $reservation->save();
                $i++;
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cv  $cv
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $cv = DB::table('cvs')->where('id', $id);
        $user_id = $request->user()->id;
        $cv_id = $id;
        $img_path = $img_path = $cv->first()->photo_md;
        if (strpos($img_path, $user_id) !== false) {
            CvImage::delete($user_id, $cv_id, $img_path);
            $path = public_path('data/'.$user_id.'/images/'.$cv_id);
            rmdir($path);
        }
        $cv->delete();
        $skills = DB::table('cv_skills')->where('cv_id', $id);
        $skills->delete();
        $schools = DB::table('cv_schools')->where('cv_id', $id);
        $schools->delete();
        $jobs = DB::table('cv_jobs')->where('cv_id', $id);
        $jobs->delete();
        $interests = DB::table('cv_interests')->where('cv_id', $id);
        $interests->delete();
        $quotes = DB::table('cv_quotes')->where('cv_id', $id);
        $quotes->delete();

    }
}
