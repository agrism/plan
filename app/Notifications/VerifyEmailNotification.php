<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject(__('app.verify_email_subject'))
            ->greeting(__('app.verify_email_greeting', ['name' => $notifiable->name]))
            ->line(__('app.verify_email_line1'))
            ->action(__('app.verify_email_action'), $verificationUrl)
            ->line(__('app.verify_email_line2', ['count' => config('auth.verification.expire', 60)]))
            ->line(__('app.verify_email_line3'))
            ->salutation(__('app.mail_salutation'));
    }
}
