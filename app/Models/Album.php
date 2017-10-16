<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    const IMAGES_DIR = 'http://www.kasivibe.com/uploads/albums/images/';
    const DEFAULT_ART = 'http://www.kasivibe.com/img/no-art.png';

    protected $fillable = [
        'name', 'image', 'active'
    ];

    protected $attributes = array(
        'name' => 'Mili',
        'image' => self::DEFAULT_ART,
    );

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($album) {
            \File::delete(self::IMAGES_DIR . $album->image);
        });

        static::created(function ($album) {
            $album->slug = $album->name;
            $album->save();
        });

    }

    public function songs()
    {
        return $this->hasMany('App\Models\Song');
    }

    public function user()
    {
        return $this->belongsTo('\App\User');
    }

    public function setSlugAttribute($value)
    {
        $slug = str_slug($value);

        if (static::whereSlug($slug)->exists()) {
            $slug = "{$slug}-" . $this->id;
        }

        $this->attributes['slug'] = $slug;
    }

    public function getImageAttribute($value)
    {
        return $value && $value != static::DEFAULT_ART
            ? static::IMAGES_DIR . $value
            : static::DEFAULT_ART;
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
