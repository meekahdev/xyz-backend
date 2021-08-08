<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'user_id', 'comment', 'comment_date'];

    public $timestamps = false;

    public function getCommentDateAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }

}
