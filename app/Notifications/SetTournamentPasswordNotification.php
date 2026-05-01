<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SetTournamentPasswordNotification extends Notification
{
    public function __construct(
        public string $token,
    )
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]));

        return (new MailMessage)
            ->line('Set your tournament password.')
            ->greeting('Hello ' . $notifiable->name . ', ')
            ->line('You have been registered to play in a hockey tournament at SHC Scoop.')
            ->line('Please set your password before the tournament by clicking the button below.')
            ->action('Set Password', $url)
            ->line('If you did not register to play in a tournament, please ignore this email.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
