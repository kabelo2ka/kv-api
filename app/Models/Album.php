<?php

namespace App\Models;

use Froiden\RestAPI\ApiModel;
use Illuminate\Database\Eloquent\Model;

class Album extends ApiModel
{
    const IMAGES_DIR = 'http://www.kasivibe.com/uploads/albums/images/';
    const DEFAULT_ART = 'http://www.kasivibe.com/img/no-art.png';

    protected $fillable = [
        'name', 'image'
    ];

    protected $attributes = array(
        'name' => 'Mili',
        'image' => 'http://www.kasivibe.com/img/no-art.png',
    );

    public function songs()
    {
        return $this->hasMany('App\Models\Song');
    }

    public function user()
    {
        return $this->belongsTo('\App\User');
    }

    public function getImageAttribute($value)
    {
        return $value
            ? self::IMAGES_DIR . $value
            : self::DEFAULT_ART;
    }

    public function setAlbumIdAttribute($value)
    {
        $value = $value == 0
            ? NULL
            : $value;
        $this->attributes['album_id'] = $value;
    }

    public function getAlbumIdAttribute($value)
    {
        if($value == NULL){
            $this->attributes['user_id'] = 0;
            $this->attributes['id'] = 0;
            $this->attributes['name'] = 'Miscellaneous';
            return 0;
        }
        return $value;
    }
}
