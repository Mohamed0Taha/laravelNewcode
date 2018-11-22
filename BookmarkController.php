<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Bookmark;
use App\User;
use App\Cv;



class BookmarkController extends Controller
{
    
    public function showBookmarks()
    {
        $id = Auth::id();
        $Bookmarks= Bookmark::where('owner_id', $id)->pluck('cv_id');
        $collection=[];
         
        foreach ($Bookmarks as &$cv_id) {
            $cv= Cv::where('id', $cv_id)->get()->toJson();
            $string = str_replace(array('[',']'),'',$cv);
            $manage = json_decode($string);
            $collection[]=$manage;
            
        }
        
        return $collection;
    }

    
    public function addBookmark($cv_id)
    {
        
        $id = Auth::id();
        $bookmark =  new Bookmark;
        $bookmark->owner_id=$id;
        $bookmark->cv_id=$cv_id;
        $bookmark->save();
        

    }
    
    
    public function removeBookmark($cv_id)
    {
        $id = Auth::id();
        $Bookmark= Bookmark::where('owner_id', $id)->where('cv_id',$cv_id)->delete();
        
    }
}
