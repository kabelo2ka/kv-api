<?php

namespace App\Models;

use Froiden\RestAPI\ApiModel;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'active',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function songs()
    {
        return $this->belongsToMany('App\Models\Song');
    }
}
