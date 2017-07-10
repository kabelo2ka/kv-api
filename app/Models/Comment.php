<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['body'];

    protected $appends = ['created_at_ago'];

    public function author()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function song()
    {
        return $this->belongsTo('App\Model\Song');
    }

    public function getCreatedAtAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

}
