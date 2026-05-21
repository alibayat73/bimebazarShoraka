<?php

use App\Models\Setting;
use Flux\Flux;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;

new #[Title('AI settings')] class extends Component {
    #[Rule('required|string')]
    public string $provider = 'openai';

    #[Rule('nullable|string')]
    public string $api_key = '';

    #[Rule('nullable|string')]
    public string $model = '';

    public function mount(): void
    {
        $this->provider = Setting::getValue('ai_provider', 'openai');
        $this->api_key = Setting::getValue('ai_api_key', '');
        $this->model = Setting::getValue('ai_model', '');
    }

    public function save(): void
    {
        $this->validate();

        Setting::setValue('ai_provider', $this->provider);
        Setting::setValue('ai_api_key', $this->api_key);
        Setting::setValue('ai_model', $this->model);

        Flux::toast(variant: 'success', text: __('AI settings have been saved.'));
    }

    public function updatedProvider(string $value): void
    {
        // Set default model when provider changes
        $this->model = match ($value) {
            'openai' => 'gpt-4o',
            'anthropic' => 'claude-3-5-sonnet-20240620',
            'gemini' => 'gemini-1.5-pro',
            default => '',
        };
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('AI settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('AI')" :subheading="__('Configure your AI provider settings for lead scoring')">
        <form wire:submit="save" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('AI Provider') }}</flux:label>
                <flux:select wire:model="provider" placeholder="{{ __('Select a provider...') }}">
                    <flux:select.option value="openai">OpenAI</flux:select.option>
                    <flux:select.option value="anthropic">Anthropic</flux:select.option>
                    <flux:select.option value="gemini">Gemini</flux:select.option>
                </flux:select>
                <flux:error name="provider" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('API Key') }}</flux:label>
                <flux:input wire:model="api_key" type="password" placeholder="sk-..." />
                <flux:error name="api_key" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Model') }}</flux:label>
                <flux:input wire:model="model" placeholder="{{ __('e.g. gpt-4o') }}" />
                <flux:error name="model" />
            </flux:field>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
