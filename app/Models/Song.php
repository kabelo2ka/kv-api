<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use JWTAuth;


class Song extends Model
{
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($song) {
            // @todo Delete audio file
            // File::delete(storage_path('songs/' . $song->file_name));
            // @todo Delete comments
            // $song->comments()->each->delete();
        });

        static::created(function ($song) {
            $song->slug = $song->name;
            $song->save();
        });

    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('name', 'file_name', 'lyrics');

    protected $appends = array('is_liked', 'likes_count', 'plays_count', 'download_link', 'is_admin', 'url', 'created_at_ago');

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

    public static function defaultAlbum()
    {
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
        } catch (\Exception $e) {
        }
        return false;
    }

    public function getIsAdminAttribute()
    {
        try {
            if ($user = JWTAuth::parseToken()->authenticate()) {
                return $this->user_id == $user->id;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    public function getUrlAttribute()
    {
        //return 'http://www.kasivibe.com/uploads/songs/' . $this->file_name;
        return '//kasivibe.com/api/v1/songs/' . $this->id . '/stream';
    }

    public function getCreatedAtAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getLikesCountAttribute()
    {
        return (int)$this->likes()->count();
    }

    public function getPlaysCountAttribute()
    {
        return (int)Redis::get('songs:' . $this->id . ':plays') | 0;
    }

    public function getLyricsAttribute($value)
    {
        return $value === NULL || $value === '' ? 'Not available.' : $value;
    }

    /**
     * Search user by keyword
     *
     * @param $query
     * @param $keyword
     * @return mixed
     */
    public function scopeSearchByKeyword($query, $keyword)
    {
        if ($keyword != '') {
            $query->where(function ($query) use ($keyword) {
                $query->where("name", "LIKE", "%$keyword%");
                //->orWhere("lyrics", "LIKE", "%$keyword%")
                //->orWhere("phone", "LIKE", "%$keyword%");
            });
        }
        return $query;
    }


    public function setSlugAttribute($value)
    {
        $slug = str_slug($value);

        if (static::whereSlug($slug)->exists()) {
            $slug = "{$slug}-" . $this->id;
        }

        $this->attributes['slug'] = $slug;
    }

    public function getDownloadLinkAttribute()
    {
        return '//kasivibe.com/api/v1/songs/' . $this->id . '/download';
    }


}
