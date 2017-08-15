<?php

namespace App;

use Froiden\RestAPI\ApiModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'phone_number', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function songs()
    {
        return $this->hasMany('App\Models\Song');
    }

    public function likedSongs()
    {
        return $this->morphedByMany('App\Models\Song', 'likeable')->whereDeletedAt(null);
    }

    public function albums()
    {
        return $this->hasMany('App\Models\Album');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }


    public function getAvatarAttribute($value)
    {
        return $value
            ? 'http://www.kasivibe.com/uploads/users/avatar/' . $value
            : 'https://www.gravatar.com/avatar/' . strtolower( trim(md5($this->attributes['email'])))

            . '?s=100&d=monsterid';
            //: 'https://www.kasivibe.com/img/no-avatar.png'

    }

    public function getArtistNameAttribute($value)
    {
        return $value ?: $this->username;
    }


    /**
     * Scope by artist
     *
     * @param $query
     * @return mixed
     */
    public function scopeArtist($query)
    {
        return $query->where('artist_name', '!=', null);
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
        if ($keyword!='') {
            $query->where(function ($query) use ($keyword) {
                $query->where("name", "LIKE", "%$keyword%")
                    ->orWhere("email", "LIKE", "%$keyword%")
                    ->orWhere("phone", "LIKE", "%$keyword%");
            });
        }
        return $query;
    }

}
