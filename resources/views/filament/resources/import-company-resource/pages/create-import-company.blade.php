@php /** @var \App\Filament\Resources\ImportCompanyResource\Pages\CreateImportCompany $this */ @endphp

<x-filament-panels::page>
    <x-filament-panels::form wire:submit.prevent="create" class="space-y-6" novalidate>
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-o-check-circle">
                Create Company
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
