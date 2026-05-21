<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function markAsRead($id): void
    {
        /** @var DatabaseNotification $notification */
        $notification = auth()->user()->notifications()->find($id);
        $notification?->markAsRead();
    }

    public function render()
    {
        return view('livewire.notification-list', [
            'notifications' => auth()->user()->notifications()->paginate(),
        ])->layout('layouts.app', ['title' => __('Notifications')]);
    }
}
