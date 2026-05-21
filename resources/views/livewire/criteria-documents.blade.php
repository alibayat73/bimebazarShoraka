<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Criteria Documents</flux:heading>
            <flux:text>Manage the reference documents used by the AI to score leads.</flux:text>
        </div>
        <div class="flex gap-4">
            <flux:button wire:click="$toggle('showForm')" icon="plus" variant="primary">Add Document</flux:button>
        </div>
    </div>

    <flux:modal wire:model="showForm" title="Add Criteria Document">
        <div class="space-y-4">
            <flux:field>
                <flux:label>Title <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="title" placeholder="e.g. High Budget Enterprise Criteria" />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>Content <span class="text-red-500">*</span></flux:label>
                <flux:textarea wire:model="content" rows="6" placeholder="Describe the criteria for scoring..." />
                <flux:error name="content" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button wire:click="$toggle('showForm')" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="addDocument" variant="primary">Save Document</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:card class="!p-0 overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Title</flux:table.column>
                <flux:table.column>Content Preview</flux:table.column>
                <flux:table.column>Embedding</flux:table.column>
                <flux:table.column>Created</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($documents as $doc)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">{{ $doc->title }}</flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate text-zinc-500">{{ Str::limit($doc->content, 80) }}</flux:table.cell>
                        <flux:table.cell>
                            @if($doc->embedding)
                                <flux:badge size="sm" color="green">Generated</flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc">Missing</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-sm text-zinc-500">{{ $doc->created_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button wire:click="deleteDocument({{ $doc->id }})" size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700 cursor-pointer">Delete</flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-6 text-zinc-500">
                            No criteria documents found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700">
            {{ $documents->links() }}
        </div>
    </flux:card>
</div>
