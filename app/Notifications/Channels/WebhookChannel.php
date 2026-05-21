<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWebhook')) {
            return;
        }

        $url = config('services.WEBHOOK.url');

        if (! $url) {
            Log::info('Webhook URL not configured, skipping webhook notification.', [
                'notifiable' => $notifiable,
            ]);

            return;
        }

        $payload = $notification->toWebhook($notifiable);

        try {
            Http::post($url, $payload);
        } catch (\Exception $e) {
            Log::error('Webhook notification failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }
}
