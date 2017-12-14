<?php

namespace App\Notifications;

use App\Models\Song;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SongPlayedMultipleTimes extends Notification
{
    use Queueable;

    public $song;

    /**
     * Create a new notification instance.
     *
     * @param Song $song
     */
    public function __construct(Song $song)
    {
        $this->song = $song;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
                    ->line("Congrats, your song \" {$this->song->name} \" has been played  {$this->song->plays_count} ". str_plural('time', count($this->song->plays_count)).'.')
                    ->action('See song', url('http://kasivibe.com/app/songs/' . $this->song->slug))
                    ->line('Thank you for using Kasivibe!');
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
            //
        ];
    }
}
