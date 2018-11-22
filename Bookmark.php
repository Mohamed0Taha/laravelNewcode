<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    
        protected $fillable = [
            'owner_id', 'user_id'
        ];
    
        public function cv()
        {
            return $this->belongsTo('App\Cv');
        }
        
    
       
    
}
