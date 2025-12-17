<?php

namespace App\Filament\Resources\HsnCodeResource\Pages;

use App\Filament\Resources\HsnCodeResource;
use App\Models\HsnCode;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateHsnCode extends CreateRecord
{
    protected static string $resource = HsnCodeResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    protected function rules(): array
    {
        return [
            'data.category_id' => ['required', 'exists:categories,id'],
            'data.code' => ['required', 'regex:/^\\d{4,8}$/', 'unique:hsn_codes,code'],
            'data.description' => ['nullable', 'string', 'max:255'],
            'data.is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'data.code.regex' => 'HSN Code must be 4 to 8 digits.',
        ];
    }

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        $this->validate();

        $data = $this->form->getState();

        HsnCode::create($data);

        Notification::make()
            ->title('HSN Code created successfully')
            ->success()
            ->send();

        $this->redirect(HsnCodeResource::getUrl('index'));
    }

    protected function hasCreateAnotherAction(): bool
    {
        // Hide the separate "Create & create another" action
        return false;
    }
}

