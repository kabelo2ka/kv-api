<?php

namespace App\Http\Controllers;

use App\Events\UserPlayedSong;
use App\Http\Requests\SongRequest;
use App\Models\Song;
use App\Notifications\SongPlayedMultipleTimes;
use Auth;
use DateTime;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use JWTAuth;
use Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class SongController extends Controller
{
    protected $model = Song::class;

    public function index()
    {
        $songs = Song::with('genre', 'user', 'album', 'comments.author')->withCount('likes')->orderBy('created_at', 'desc')->get();
        return response(array('data' => $songs), 200);
    }

    public function trending(Request $request)
    {
        $limit = $request->get('limit');

        $songs = Song::getTrending($limit);
        return response()->json(['data'=>$songs], 200);
    }

    public function destroy($slug)
    {
        if ($song = Auth::user()->songs()->whereSlug($slug)->firstOrFail()) {
            $song->delete();
            return response()->json(['msg' => 'success'], 200);
        }
        return response(404);
    }

    public function show($slug)
    {
        $song = Song::whereSlug($slug)->with(
            [
                'comments' => function ($query) {
                    return $query->orderByDesc('created_at')->with('author')->get();
                },
                'genre', 'album', 'user'
            ]
        )->first();

        return response()->json(['data' => $song], 200);
    }

    public function authSongs()
    {
        $user = Auth::user();
        $songs = $user->songs()->get();

        return response(['data' => $songs], 200);

    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $song = Auth::user()->songs()->whereId($id)->firstOrFail();
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

    public function store(SongRequest $request)
    {
        $temp_file = storage_path('app/uploads/tmp/').$request->get('file_name');
        $new_file = public_path('uploads/songs/').$request->get('file_name');
        if( ! File::move($temp_file, $new_file)){
            return response()->json(['error' => 'Could not move file'], 500);
        };
        $song = new Song($request->all());

        if($request->get('album_id') === 'create'){
            $this->validate($request, [
                'album_name' => "required|min:3|unique:albums,name,NULL,id,user_id," . Auth::id()
            ], [
                'album_name.unique' => 'You already have an album named "' . $request->get('album_name') . '".'
            ]);
            $album_name = $request->get('album_name');
            $album = Auth::user()->albums()->create(['name' => $album_name]);
            $album = $album->save();
            $song->album_id = $album->id;
        }else{
            $song->album_id = $request->get('album_id');
        }

        $song->genre_id = $request->get('genre_id');
        $song->user()->associate(Auth::user());
        $result = $song->save();
        return response()->json(['data'=>$result], 200);
    }

    /**
     * Upload Audio File
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFile(Request $request)
    {
        $audio_file = $request->file('file');
        $ext = $audio_file->getClientOriginalExtension();
        $ext = ($ext === 'mpga' || $ext === 'bin') ? 'mp3' : $ext;
        $filename = md5($audio_file->getClientOriginalName() . microtime()) . '.' . $ext;
        $location = storage_path('app/uploads/tmp/');
        if ($audio_file->move($location, $filename)) {
            return response()->json(['filename' => $filename], 200);
        } else {
            return response()->json(['error' => 'Audio file not saved'], 413);
        }
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $songs = Song::SearchByKeyword($query)->with('user', 'genre', 'album')->take(10)->get();
        return response()->json(['data'=>$songs], 200);
    }

    public function stream($id)
    {
        $song = Song::findOrFail($id);
        $filename = public_path('uploads/songs/' . $song->file_name);

        if (file_exists($filename)) {
            // Return audio stream
            $response = new BinaryFileResponse($filename);
            BinaryFileResponse::trustXSendfileTypeHeader();
            return $response;
        }
        return response()->json(['error'=>'File Not Found'], 404);
    }


    public function download(Request $request, $id)
    {
        $song = Song::findOrFail($id);

        if( $song->downloadable ){
            $filename = public_path('uploads/songs/' . $song->file_name);
            // check if file exists
            if ( ! file_exists($filename)) {
                return response()->json('File Not Found', 404);
            }

            // Record download on redis
            // @todo don't record multiple downloads from one IP in a certain time period
            Redis::incr('songs:' . $song->id . ':downloads');
            // Create UserDownloadedSongEvent
            //event(new UserPlayedSong($song->id));

            // Generate file and headers
            $filesize = (int)File::size($filename);
            $file = File::get($filename);
            $response = Response::make($file, 200);
            $response->header('Content-Type', 'File Transfer');
            $response->header('Content-Description', 'audio/mpeg');
            $response->header('Content-Disposition', 'attachment; filename=' .sprintf('"%s [www.kasivibe.com].mp3"', addcslashes(basename($song->name), '"\\')));
            $response->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            $response->header('Expires', '0');
            $response->header('Connection', 'Keep-Alive');
            $response->header('Content-Transfer-Encoding', 'binary');
            $response->header('Content-Type', 'audio/mpeg');
            $response->header('Content-Length', $filesize);

            return $response;
        }
        return response()->json(['error' => 'Song is not downloadable.'], 403);
    }
    

    public function storePlay(Request $request)
    {
        $song_id = $request->get('song_id');
        $date = date('dmY');
        $user_ip = $request->ip(); // For unique plays
        $play_key = 'songs:' . $song_id . ':ip:' . $user_ip .':date:' . $date . ':plays';

        // if there's a stored play - User can only record a play once a day
        /*if ( Redis::get($play_key) ) {
            return response()->json(['data' => 'already_recorded'], 208);
        }*/

        // 1. Publish Event
        // 2. Node.js + Redis subscribes to the event
        // 3. Use socket.io to emit to all subscribed clients
        Redis::incr($play_key);
        event(new UserPlayedSong($song_id));

        //Send notification to song owner
        $song = Song::whereId($song_id)->firstOrFail();
        $song_owner = $song->user;
        $song_owner->notify(new SongPlayedMultipleTimes($song));

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
