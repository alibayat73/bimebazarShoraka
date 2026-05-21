<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function render()
    {
        return view('livewire.notification-list', [
            'notifications' => auth()->user()->notifications()->paginate(15),
        ])->layout('layouts.app', ['title' => __('Notifications')]);
    }
}
