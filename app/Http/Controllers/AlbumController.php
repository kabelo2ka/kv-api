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
        $albums = Album::with('user')->orderByDesc('created_at')->get();
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

    public function store(Request $request)
    {
        $this->validate($request, [
            'album_name' => "required|min:3|unique:albums,name,NULL,id,user_id," . Auth::id()
        ], [
            'album_name.unique' => 'You already have an album named "' . $request->get('album_name') . '".'
        ]);
        $album_name = $request->get('album_name');
        $album_image = $request->get('album_image');
        $album = Auth::user()->albums()->create(['name' => $album_name, 'image' => $album_image]);

        return response()->json(['data'=>$album],200);
    }


}
