<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;
use Auth;

class LikeController extends Controller
{
    public function likeSong(Request $request)
    {
        // here you can check if song exists or is valid or whatever

        $handle_like = $this->handleLike('App\Models\Song', $request->get('id'));
        return response(['data'=> $handle_like], 200);
    }

    public function likeComment(Request $request)
    {
        // here you can check if comment exists or is valid or whatever

        $this->handleLike('App\Models\Comment',  $request->get('id'));
        return response(['data'=>'success'], 200);
    }

    public function handleLike($type, $id)
    {
        $existing_like = Like::withTrashed()->whereLikeableType($type)->whereLikeableId($id)->whereUserId(Auth::id())->first();

        if (is_null($existing_like)) {
            Like::create([
                'user_id'       => Auth::id(),
                'likeable_id'   => $id,
                'likeable_type' => $type,
            ]);
        } else {
            if (is_null($existing_like->deleted_at)) {
                $existing_like->delete();
                return 'Unliked';
            } else {
                $existing_like->restore();
            }
        }
        return 'Liked';
    }

}
