<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ \App\Filament\Resources\IndentResource::getUrl('create') }}"
                class="fi-btn inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
            >
                <x-heroicon-o-plus class="h-5 w-5" />
                <span>Create Indent</span>
            </a>

            <a
                href="{{ \App\Filament\Resources\IndentResource::getUrl('index') }}"
                class="fi-btn inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
            >
                <x-heroicon-o-clipboard-document-list class="h-5 w-5" />
                <span>View Indents</span>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
