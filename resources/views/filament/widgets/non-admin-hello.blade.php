<x-filament-widgets::widget>
    Hello {{ auth()->user()?->name ?? 'User' }}!
 
</x-filament-widgets::widget>
