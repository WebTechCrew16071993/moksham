<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\DocumentPermission;
use App\Services\ActivityLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(fn (): bool => Auth::user()?->isAdmin() ?? false),
        ];
    }

    protected function authorizeAccess(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pre-fill permissions for non-admin users
        $resources = \App\Filament\Resources\UserResource::permissionResources();
        $existing = DocumentPermission::query()
            ->where('user_id', $this->record->getKey())
            ->get()
            ->keyBy('resource');

        $data['permissions'] = collect($resources)
            ->keys()
            ->map(function ($key) use ($existing) {
                $row = $existing->get($key);
                return [
                    'resource' => $key,
                    'can_view' => (bool) ($row->can_view ?? false),
                    'can_create' => (bool) ($row->can_create ?? false),
                    'can_update' => (bool) ($row->can_update ?? false),
                    'can_delete' => (bool) ($row->can_delete ?? false),
                ];
            })->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getRawState();
        $perms = $data['permissions'] ?? [];

        // Build BEFORE state from DB
        $before = DocumentPermission::query()
            ->where('user_id', $this->record->getKey())
            ->get()
            ->map(fn($p) => [
                'resource' => $p->resource,
                'can_view' => (bool) $p->can_view,
                'can_create' => (bool) $p->can_create,
                'can_update' => (bool) $p->can_update,
                'can_delete' => (bool) $p->can_delete,
            ])->keyBy('resource')->toArray();

        foreach ($perms as $row) {
            if (!isset($row['resource'])) {
                continue;
            }

            DocumentPermission::updateOrCreate(
                [
                    'user_id' => $this->record->getKey(),
                    'resource' => $row['resource'],
                ],
                [
                    'can_view' => (bool) ($row['can_view'] ?? false),
                    'can_create' => (bool) ($row['can_create'] ?? false),
                    'can_update' => (bool) ($row['can_update'] ?? false),
                    'can_delete' => (bool) ($row['can_delete'] ?? false),
                ]
            );
        }

        // AFTER state from form
        $after = collect($perms)->keyBy('resource')->toArray();

        // Log activity for permission updates with before/after
        // ActivityLogger::log('permissions.updated', [
        //     'user_id' => $this->record->getKey(),
        //     'subject_type' => 'user',
        //     'subject_id' => $this->record->getKey(),
        //     'subject_label' => $this->record->name,
        //     'description' => 'Permissions updated for user',
        //     'changes' => ActivityLogger::diff($before, $after),
        // ]);
    }
}
