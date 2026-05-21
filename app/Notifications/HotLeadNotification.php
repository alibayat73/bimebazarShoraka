<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Notifications\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HotLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Lead $lead)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail', WebhookChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔥 Hot Lead Alert: '.$this->lead->name)
            ->greeting('Hello Sales Team,')
            ->line('A new high priority lead has been ingested.')
            ->line('Name: '.$this->lead->name)
            ->line('Score: '.$this->lead->score)
            ->line('Budget: $'.number_format((float) $this->lead->budget, 2))
            ->action('View Dashboard', url('/dashboard'))
            ->line('Thank you for using our Lead Management System!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'name' => $this->lead->name,
            'score' => $this->lead->score,
            'priority' => $this->lead->priority,
            'message' => 'New Hot Lead: '.$this->lead->name.' (Score: '.$this->lead->score.')',
        ];
    }

    /**
     * Get the webhook representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toWebhook(object $notifiable): array
    {
        return [
            'event' => 'lead.hot',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'id' => $this->lead->id,
                'name' => $this->lead->name,
                'email' => $this->lead->email,
                'phone' => $this->lead->phone,
                'budget' => (float) $this->lead->budget,
                'source' => $this->lead->source,
                'score' => $this->lead->score,
                'priority' => $this->lead->priority,
            ],
        ];
    }
}
