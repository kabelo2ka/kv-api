<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use JWTAuth;


class Song extends Model
{
    const SONGS_DIR = 'http://www.kasivibe.com/uploads/songs/';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('name', 'file_name', 'lyrics', 'downloadable', 'commentable', 'private');

    protected $appends = array('is_liked', 'likes_count', 'comments_count', 'downloads_count', 'plays_count', 'download_link', 'is_admin', 'url', 'created_at_ago');

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($song) {
            // @todo Research: Does soft delete fire this event? If yes, don't the song's related data.
            /*// Delete audio file
            \File::delete(self::SONGS_DIR . $song->name);
            // Delete comments
            $song->comments()->each->delete();
            // Delete Plays Count
            Redis::del('songs:' . $song->id . ':plays');
            // Delete Downloads Count
            Redis::del('songs:' . $song->id . ':downloads');*/

        });

        static::created(function ($song) {
            $song->slug = $song->name;
            $song->save();
        });

    }

    public static function defaultAlbum()
    {
        return [
            'id'    => 0,
            'name'  => 'Miscellaneous',
            'image' => 'http://www.kasivibe.com/img/no-art.png'
        ];
    }

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

    public function likes()
    {
        return $this->morphToMany('\App\User', 'likeable');
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
        // return '//www.kasivibe.com/uploads/songs/' . $this->file_name;
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

    public function getCommentsCountAttribute()
    {
        return (int)$this->comments()->count();
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function getPlaysCountAttribute()
    {
        return (int)count(Redis::keys('songs:' . $this->id . ':ip*:plays')) | 0;
    }

    public function getDownloadsCountAttribute()
    {
        return (int)Redis::get('songs:' . $this->id . ':downloads') | 0;
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
