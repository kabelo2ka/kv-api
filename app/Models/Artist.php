<?php

namespace App\Models;

use Froiden\RestAPI\ApiModel;
use Illuminate\Database\Eloquent\Model;

class Artist extends ApiModel
{
    protected $fillable = [
        'name', 'verified'
    ];

    public function songs()
    {
        return $this->belongsToMany('App\Models\Song');
    }

    /**
     * Search by keyword
     *
     * @param $query
     * @param $keyword
     * @return mixed
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where("name", "LIKE","%$keyword%");
        }
        return $query;
    }


}
