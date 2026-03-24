<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TrainingCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $training;

    /**
     * Create a new notification instance.
     *
     * @param $training
     */
    public function __construct($training)
    {
        $this->training = $training;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tréning bol zrušený')
            ->greeting('Dobrý deň, ' . $notifiable->name)
            ->line('S poľutovaním vám oznamujeme, že tréning s názvom "' . $this->training->title . '" bol zrušený.')
            ->line('Dátum tréningu: ' . $this->training->start_at->format('d.m.Y H:i'))
            ->line('Ak máte akékoľvek otázky, neváhajte nás kontaktovať.')
            ->salutation('S pozdravom, Tím Rezervačného systému');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'training_id' => $this->training->id,
            'title' => $this->training->title,
            'start_at' => $this->training->start_at,
        ];
    }
}
