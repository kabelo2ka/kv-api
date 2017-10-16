<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlbumRequest;
use App\Models\Album;
use Auth;
use Illuminate\Http\Request;

class AlbumController extends Controller
{

    public function index()
    {
        $albums = Album::with('user')->orderByDesc('created_at')->get();
        return response(['data' => $albums], 200);
    }

    public function show($slug)
    {
        $album = Album::whereSlug($slug)->with('user', 'songs.user', 'songs.album')->first();
        return response(['data' => $album], 200);
    }

    public function authAlbums()
    {
        $user = Auth::user();
        $albums = $user->albums()->get();

        return response(['data' => $albums], 200);

    }

    public function store(AlbumRequest $request)
    {
        if ($request->has('imageData') && '' !== $imageData = $request->get('imageData')) {
            $image = base64_decode($imageData);
            $filename = md5($request->get('album_name') . microtime()) . '.jpg';
            $path = public_path('uploads/albums/images/' . $filename);
            \Image::make($image)->resize(512,512)->save($path);
            $request->merge(['image'=>$filename]);
        }

        $album = Auth::user()->albums()->create($request->all());

        return response()->json(['data' => $album], 200);
    }

    public function update(AlbumRequest $request, $slug)
    {
        $album = Auth::user()->albums()->whereSlug($slug)->firstOrFail();
        if ($request->has('imageData') && '' !== $imageData = $request->get('imageData')) {
            $image = base64_decode($imageData);
            $filename = md5($request->get('album_name') . microtime()) . '.jpg';
            $path = public_path('uploads/albums/images/' . $filename);
            \Image::make($image)->resize(512,512)->save($path);
            \File::delete(public_path('uploads/albums/images/' . $filename));
            $request->merge(['image'=>$filename]);
        }

        $album->update($request->except('imageData'));

        return response()->json(['data' => $album], 200);
    }

    public function destroy($slug)
    {
        $album = auth()->user()->albums()->whereSlug($slug)->firstOrFail();
        $album->delete();
        return response()->json(['data', 'Deleted'], 200);
    }

}
