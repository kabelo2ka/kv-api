<?php

namespace App\Http\Controllers;

use App\Events\UserCommented;
use App\Models\Comment;
use Illuminate\Http\Request;
use Auth;
use App\Models\Song;

class SongCommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $song_id
     * @return \Illuminate\Http\Response
     */
    public function index($song_id)
    {
        $comments = Comment::whereId($song_id)->get();
        return response()->json(['data' => $comments], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $song 'Song Id'
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $song)
    {
        $comment = new Comment();
        $comment->user_id = Auth::id();
        $comment->song_id = $song;
        $comment->body = $request->get('body');
        $comment->save();
        //event(new UserCommented($comment));
        broadcast(new UserCommented($comment))->toOthers();
        return response()->json(['data'=>'success'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comments
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comment  $comments
     * @return \Illuminate\Http\Response
     */
    public function edit(Comment $comments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comments
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comments)
    {
        $comment = Auth::user()->comments()->findOrfail($comments);
        $comment->update( ['body'=>$request->get('body')] );
        return response()->json(['data'=>'success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comments
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comments)
    {
        $comment = Auth::user()->comments()->findOrfail($comments);
        $comment->delete();
        return response()->json(['data'=>'success'], 200);
    }
}
