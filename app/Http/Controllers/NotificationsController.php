<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\User;
use Auth;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications->take(6);
        $formatted_notifications = [];
        foreach ($notifications as $notification){
            // If user commented on song
            if($notification->type === 'App\Notifications\UserCommented'){
                $data = $notification->data;
                $comment = Comment::whereId($data['comment_id'])->with(['author', 'song'])->firstOrFail();
                $formatted_notifications[] = [
                    'comment' => $comment,
                    'author' => $comment['author'],
                    'song' => $comment['song'],
                ];
            }
        }
        return response()->json(['data'=>$formatted_notifications], 200);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->unreadNotifications()->firstOrFail($id);
        $notification->markAsRead();
        return response()->json(['msg'=>'success'], 200);
    }
}
