<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'status', 'post_date', 'posted_by'];

    public $timestamps = false;

    public function getPostDateAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }


    public function comments(){
        return $this->hasMany('\App\Models\Comment','post_id','id')
                    ->join('users','users.id','=','comments.user_id')
                    ->select("users.name", 'comments.*');
    }

    // public function comments(){
    //     return $this->hasMany(Comment::class, 'post_id', 'id');
    // }

    // public function commentsCount()
    // {
    //     return $this->hasOne('\App\Models\Comment','post_id','id')
    //                 ->selectRaw('post_id, count(*) as total_count')
    //                 ->groupBy('post_id');
    // }

    // public function getCommentsCountAttribute()
    // {
    //     if ($this->commentsCount) return $this->commentsCount->count;
            
    //     return 0;

    // }

 

}
