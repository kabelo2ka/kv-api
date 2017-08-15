<?php

namespace App\Models;

use Froiden\RestAPI\ApiModel;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Illuminate\Support\Facades\Redis;
use JWTAuth;
use Carbon\Carbon;


class Song extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'file_name', 'lyrics',
    ];

    protected $appends = array('is_liked', 'likes_count', 'plays_count', 'is_admin', 'album', 'url', 'created_at_ago');

    public function artists()
    {
        return $this->belongsToMany('App\Models\Artist');
    }

    public function genre()
    {
        return $this->belongsTo('App\Models\Genre');
    }

    public function album()
    {
        return $this->belongsTo('App\Models\Album');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function getAlbumAttribute()
    {
        //if (!$this->relationLoaded('album')) $this->load('album');

        //return $this->getRelation('album') ?: self::defaultAlbum();
    }

    public static function defaultAlbum(){
        return [
            'id' => 0,
            'name' => 'Miscellaneous',
            'image' => 'http://www.kasivibe.com/img/no-art.png'
        ];
    }

    public function likes()
    {
        return $this->morphToMany('\App\User', 'likeable');
    }

    public function getIsLikedAttribute()
    {
        try {
            if ($user = JWTAuth::parseToken()->authenticate()) {
                $like = $this->likes()->whereUserId($user->id)->first();
                return !is_null($like) ? true : false;
            }
        }  catch (\Exception $e) {}
        return false;
    }

    public function getIsAdminAttribute()
    {
        try {
            if ($user = JWTAuth::parseToken()->authenticate()) {
                return $this->user_id == $user->id;
            }
        }  catch (\Exception $e) {}
        return false;
    }

    public function getUrlAttribute()
    {
        //return 'http://www.kasivibe.com/uploads/songs/' . $this->file_name;
        return 'http://www.kasivibe.com/api/v1/songs/' . $this->id . '/stream';
    }

    public function getCreatedAtAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getLikesCountAttribute()
    {
        return (int) $this->likes()->count();
    }

    public function getPlaysCountAttribute()
    {
        return (int) Redis::get('songs:' . $this->id . ':plays') | 0;
    }


}
