<?php

namespace App\Http\Controllers;

use Froiden\RestAPI\ApiController;
use App\Models\Album;
use Illuminate\Http\Request;
use Auth;

class AlbumController extends Controller
{

    public function index()
    {
        $albums = Album::with('user')->get();
        return response(['data' => $albums], 200);
    }

    public function show($id)
    {
        $album = Album::whereId($id)->with('user', 'songs.user', 'songs.album')->first();
        return response(['data' => $album], 200);
    }

    public function authAlbums()
    {
        $user = Auth::user();
        $albums = $user->albums()->get();

        return response(['data' => $albums], 200);

    }


}
