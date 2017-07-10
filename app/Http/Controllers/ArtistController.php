<?php

namespace App\Http\Controllers;

use App\User;
use Froiden\RestAPI\ApiController;
use Illuminate\Http\Request;

class ArtistController extends Controller
{

    public function index()
    {
        $artists = User::where('artist_name', '!=', null)->get();
        return response(['data' => $artists], 200);
    }


    public function show($id)
    {
        $artist = User::whereId($id)->where('artist_name', '!=', null)->with('albums', 'songs.user', 'songs.album')->first();
        return response(['data' => $artist], 200);
    }
















}