<?php

namespace App\Http\Controllers;

use App\Events\UserPlayedSong;
use App\Models\Song;
use Auth;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use JWTAuth;
use Response;

class SongController extends Controller
{
    protected $model = Song::class;

    public function index()
    {
        $songs = Song::with('genre', 'user', 'album', 'comments.author')->withCount('likes')->orderBy('created_at', 'desc')->get();
        return response(array('data' => $songs), 200);
    }

    public function delete($id)
    {
        if ($song = Auth::user()->songs()->whereId($id)->delete()) {
            return response(200);
        }
        return response(404);
    }

    public function show($id)
    {
        $song = Song::whereId($id)->with(
            [
                'comments' => function ($query) {
                    return $query->orderByDesc('created_at')->with('author')->get();
                },
                'genre', 'album', 'user'
            ]
        )->first();

        return response()->json(['data' => $song], 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $song = Auth::user()->songs()->whereId($id)->first();
        if ($request->hasFile('file')) {
            // Delete previous audio file
            try{
                \Storage::delete(public_path('uploads/songs/'.$song->file_name));
            }catch (\Exception $e){
                // @todo Log error to file
            }
            //Save new audio file in Directory
            $audio_file = $request->file('file');
            $ext = $audio_file->extension();
            $ext = ($ext === 'mpga' || $ext === 'bin') ? 'mp3' : $audio_file->extension();
            $filename = md5($audio_file->getClientOriginalName() . microtime()) . '.' . $ext;
            $location = public_path('uploads/songs/');
            if (!$audio_file->move($location, $filename)) {
                return response()->json(['error' => 'Audio file not saved'], 413);
            }
            $song->file_name = $filename;
        }

        $song->album_id = $request->get('album_id');
        $song->genre_id = $request->get('genre_id');
        // Save to database
        $song->fill($request->all())->save();
        return response()->json(['data' => $song], 200);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|min:3|unique:songs,name,NULL,id,album_id,' . $request->get('album_id') . ',user_id,"' . Auth::id(),
            'file'     => 'required|mimes:mpga,bin,wav,ogg',
            'genre_id' => 'required',
            'album_id' => 'required',
        ], [
            'name.unique' => 'You already have a song titled "' . $request->get('name') . '" in the selected album.'
        ]);

        // Create new album if album id == "create".
        if ($request->get('album_id') == 'create') {
            $this->validate($request, [
                'album_name' => "required|min:3|unique:albums,name,NULL,id,user_id," . Auth::id()
            ], [
                'album_name.unique' => 'You already have an album named "' . $request->get('album_name') . '".'
            ]);
            $album_name = $request->get('album_name');
            $album_image = $request->get('album_image');
            $album = Auth::user()->albums()->create(['name' => $album_name, 'image' => $album_image]);
            $request->merge(['album_id' => $album->id]);
        } elseif ($request->get('album_id') == 0) {
            $request->merge(['album_id' => NULL]);
        }

        if ($request->hasFile('file')) {
            //Save audio file in Directory
            $audio_file = $request->file('file');
            $ext = $audio_file->extension();
            $ext = ($ext === 'mpga' || $ext === 'bin') ? 'mp3' : $audio_file->extension();
            $filename = md5($audio_file->getClientOriginalName() . microtime()) . '.' . $ext;
            $location = public_path('uploads/songs/');

            if ($audio_file->move($location, $filename)) {
                // Create song
                $song = new Song($request->all());
                $song->file_name = $filename;
                $song->album_id = $request->get('album_id');
                $song->genre_id = $request->get('genre_id');

                // Associate song to authenticated user
                $song->user()->associate(Auth::user());
                // Save to database
                $song->save();
                return response()->json(['data' => $song], 200);
            } else {
                return response()->json(['error' => 'Audio file not saved'], 413);
            }
        } else {
            // File not uploaded
            return $this->processUploadError($request);
        }
    }

    public function stream($id)
    {
        //$filename = base_path('resources/audio/' . $id . '.mp3');
        $song = Song::findOrFail($id);
        $filename = public_path('uploads/songs/' . $song->file_name);
        if (file_exists($filename)) {
            $filesize = (int)File::size($filename);
            $file = File::get($filename);
            $mime_type = 'audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3';
            $response = Response::make($file, 200);
            $response->header('Content-Type', 'audio/mpeg');
            $response->header('Content-Length', $filesize);
            $response->header('X-Pad', 'avoid browser bug');
            //$response->header('Content-Disposition', 'filename="' . $song->file_name . '"');
            $response->header('Cache-Control', 'no-cache');
            $response->header('Accept-Ranges', 'bytes');
            $response->header('Content-Range', 'bytes 0-' . $filesize . '/' . $filesize);
            return $response;
        } else {
            return response()->json('File Not Found', 404);
        }
    }

    public function storePlay(Request $request)
    {
        // 1. Publish Event
        // 2. Node.js + Redis subscribes to the event
        // 3. Use socket.io to emit to all subscribed clients
        //$user_ip = $request->ip(); // For unique plays
        $song_id = $request->get('song_id');
        Redis::incr('songs:' . $song_id . ':plays');
        event(new UserPlayedSong($song_id));
        return response()->json(['data' => 'success'], 200);
    }

    public function processUploadError($request)
    {
        switch ($request->file('file')->getError()) {
            case 1:
            case 2:
                $error = 'The uploaded file was to large.';
                break;
            case 3:
                $error = 'The file was only partially uploaded.';
                break;
            case 6:
            case 7:
            case 8:
                $error = 'The file could not be uploaded due to a system error.';
                break;
            case 4:
            default:
                $error = 'No file was uploaded.';
        }
        // File not uploaded
        return response()->json(['error' => $error], 401);
    }

}
