<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Song;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserCommented extends Notification
{
    use Queueable;

    public $comment;
    public $author;
    public $song;

    /**
     * Create a new notification instance.
     *
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->author = User::findOrFail($comment->user_id);
        $this->song = Song::findOrFail($comment->song_id);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // return ['mail'];
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('Someone has commented on your song.')
            ->action('View Comment', url('/songs/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'comment_id' => $this->comment->id,
            'song_id' => $this->comment->song_id,
        ];
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage([
            'comment_id' => $this->comment->id,
            'song_id' => $this->comment->song_id,
        ]);
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'comment' => $this->comment,
            'author' => $this->author,
            'song' => $this->song,
        ]);
    }

    public function broadcastOn()
    {
        return ['songs'];
    }
}
