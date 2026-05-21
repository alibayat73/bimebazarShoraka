<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Notifications</flux:heading>
            <flux:text>Stay updated on high priority leads.</flux:text>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <div class="flex gap-4">
                <flux:button wire:click="markAllAsRead" icon="check-badge" variant="subtle">Mark all as read</flux:button>
            </div>
        @endif
    </div>

    <div class="space-y-4">
        @forelse($notifications as $notification)
            <flux:card class="{{ $notification->unread() ? 'border-l-4 border-l-orange-500 dark:border-l-orange-500' : '' }} transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex gap-4 items-start">
                        <div class="mt-1">
                            @if($notification->unread())
                                <flux:icon.bell-alert class="text-orange-500" variant="solid" />
                            @else
                                <flux:icon.bell class="text-zinc-400" />
                            @endif
                        </div>
                        <div>
                            <flux:heading size="md" class="{{ $notification->unread() ? 'font-bold' : '' }}">
                                {{ $notification->data['message'] ?? 'New Notification' }}
                            </flux:heading>
                            <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 space-y-1">
                                @if(isset($notification->data['score']))
                                    <p>Score: <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $notification->data['score'] }}</span></p>
                                @endif
                                @if(isset($notification->data['priority']))
                                    <p>Priority: <flux:badge size="sm" color="orange">{{ $notification->data['priority'] }}</flux:badge></p>
                                @endif
                            </div>
                            <div class="mt-3">
                                <flux:text class="text-xs text-zinc-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </flux:text>
                            </div>
                        </div>
                    </div>
                    
                    @if($notification->unread())
                        <flux:button wire:click="markAsRead('{{ $notification->id }}')" size="sm" variant="ghost">
                            Mark as read
                        </flux:button>
                    @endif
                </div>
            </flux:card>
        @empty
            <flux:card class="text-center py-10">
                <flux:icon.bell-slash class="mx-auto mb-3 text-zinc-400" size="xl" />
                <flux:heading>No notifications yet</flux:heading>
                <flux:text>When new high priority leads arrive, they will show up here.</flux:text>
            </flux:card>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
