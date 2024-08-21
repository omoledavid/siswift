<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class MessageReceivedNotification extends Notification
{
    use Queueable;

    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail']; // You can add 'database', 'sms', etc.
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('New Message Received')
            ->line('You have received a new message regarding your product.')
            ->line('Message: ' . $this->message->message)
            ->action('View Conversation', url('/conversations/' . $this->message->conversation_id))
            ->line('Thank you for using our application!');

        if ($this->message->files->count() > 0) {
            foreach ($this->message->files as $file) {
                $mail->line('File: ' . url(Storage::url($file->file_path)));
            }
        }

        return $mail;
    }
}
