<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Lead Management</flux:heading>
            <flux:text>Manage and generate leads in real-time.</flux:text>
        </div>
        <div class="flex gap-4">
            <flux:button wire:click="$toggle('showForm')" icon="plus" variant="primary">Generate Lead</flux:button>
        </div>
    </div>

    <flux:modal wire:model="showForm" title="Generate Lead">
        <div class="space-y-4">
            <flux:field>
                <flux:label>Name <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="name" placeholder="John Doe" />
                <flux:error name="name" />
            </flux:field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="john@example.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input wire:model="phone" placeholder="09121234567" />
                    <flux:error name="phone" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Budget</flux:label>
                    <flux:input wire:model="budget" type="number" step="0.01" placeholder="50000" />
                    <flux:error name="budget" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button wire:click="$toggle('showForm')" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="generateLead" variant="primary">Create Lead</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <flux:card>
            <flux:heading size="lg">{{ $totalLeads }}</flux:heading>
            <flux:text>Total Leads</flux:text>
        </flux:card>
        
        <flux:card>
            <flux:heading size="lg" class="text-orange-500">{{ $highPriorityLeads }}</flux:heading>
            <flux:text>High Priority (Hot)</flux:text>
        </flux:card>
        
        <flux:card>
            <flux:heading size="lg">${{ number_format($avgBudget, 2) }}</flux:heading>
            <flux:text>Average Budget</flux:text>
        </flux:card>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search leads by name or email..." />
        </div>
        <div class="w-full sm:w-48">
            <flux:select wire:model.live="priorityFilter" placeholder="All Priorities">
                <flux:select.option value="">All Priorities</flux:select.option>
                <flux:select.option value="High">High</flux:select.option>
                <flux:select.option value="Medium">Medium</flux:select.option>
                <flux:select.option value="Low">Low</flux:select.option>
            </flux:select>
        </div>
    </div>

    <!-- Leads Table -->
    <flux:card class="!p-0 overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Contact</flux:table.column>
                <flux:table.column>Budget</flux:table.column>
                <flux:table.column>Source</flux:table.column>
                <flux:table.column>Score</flux:table.column>
                <flux:table.column>Priority</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($leads as $lead)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">{{ $lead->name }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col text-sm">
                                <span>{{ $lead->email ?: 'N/A' }}</span>
                                <span class="text-zinc-500">{{ $lead->phone ?: 'N/A' }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>${{ number_format($lead->budget, 2) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $lead->source ?: 'Unknown' }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="font-medium">{{ $lead->score }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="match($lead->priority) {
                                'High' => 'orange',
                                'Medium' => 'yellow',
                                default => 'zinc',
                            }">
                                {{ $lead->priority }}
                            </flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-6 text-zinc-500">
                            No leads found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
        
        <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700">
            {{ $leads->links() }}
        </div>
    </flux:card>
</div>

