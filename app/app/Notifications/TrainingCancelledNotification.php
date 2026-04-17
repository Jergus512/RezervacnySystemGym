<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TrainingCancelledNotification extends Notification
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
        \Log::info("Sending training cancellation email to: " . $notifiable->email);

        $logoUrl = url('img/logo1.png');

        return (new MailMessage)
            ->subject('Oznámenie o zrušení tréningu')
            ->markdown('mail.training-cancelled', [
                'userName' => $notifiable->name,
                'trainingTitle' => $this->training->title,
                'trainingDate' => $this->training->start_at->format('d.m.Y o H:i'),
                'logoUrl' => $logoUrl,
            ]);
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

    /**
     * Get the training associated with the notification.
     */
    public function getTraining()
    {
        return $this->training;
    }
}
