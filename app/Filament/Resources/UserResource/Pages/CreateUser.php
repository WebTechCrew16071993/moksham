<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\DocumentPermission;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    protected function getRedirectUrl(): string
    {
        // After create, go back to the Users listing page
        return static::getResource()::getUrl('index');
    }

    protected function hasCreateAnotherAction(): bool
    {
        // Hide the separate "Create & create another" action
        return false;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();
        $perms = $data['permissions'] ?? [];

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

        // Log activity for initial permissions set
        // ActivityLogger::log('permissions.created', [
        //     'user_id' => $this->record->getKey(),
        //     'subject_type' => 'user',
        //     'subject_id' => $this->record->getKey(),
        //     'subject_label' => $this->record->name,
        //     'description' => 'Initial permissions set for user',
        //     'changes' => [
        //         'after' => collect($perms)->keyBy('resource')->toArray(),
        //     ],
        // ]);
    }
}
