<?php

namespace App\Filament\Resources\ImportCompanyResource\Pages;

use App\Filament\Resources\ImportCompanyResource;
use App\Models\ImportCompany;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateImportCompany extends CreateRecord
{
    protected static string $resource = ImportCompanyResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    protected static string $view = 'filament.resources.import-company-resource.pages.create-import-company';

    

    /**
     * Validation rules for the Create page.
     * Use the 'data.*' keys because Filament stores form state in the 'data' array by default.
     */
    protected function rules(): array
    {
        return [
            'data.name' => ['required', 'string', 'max:255'],
            'data.person_name' => ['required', 'string', 'max:255'],
            'data.address' => ['required', 'string', 'max:2000'],
            'data.city' => ['required', 'string', 'max:255'],
            'data.state' => ['required', 'string', 'max:255'],
            // Zip code must be 5 or 6 digits
            'data.zip' => ['required', 'digits_between:5,6'],
            'data.country' => ['required', 'string', 'max:255'],
            'data.iec' => ['nullable', 'string', 'max:255'],
            'data.gstin' => ['nullable', 'string', 'max:255'],
            'data.pan' => ['nullable', 'string', 'max:255'],
            'data.email' => ['required', 'email', 'max:255'],
            'data.phone_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\s-]+$/'],
            'data.notes' => ['nullable', 'string'],

            'data.bank_name' => ['required', 'string', 'max:255'],
            'data.bank_address' => ['required', 'string', 'max:2000'],
            'data.bank_city' => ['required', 'string', 'max:255'],
            'data.bank_state' => ['required', 'string', 'max:255'],
            'data.bank_zip' => ['required', 'digits_between:5,6'],
            'data.bank_country' => ['required', 'string', 'max:255'],
            // A/C No must be digits only; enforce 1-30 digits
            'data.bank_account_number' => ['required', 'digits_between:1,30'],
            'data.bank_swift_code' => ['nullable', 'string', 'max:255'],
            'data.bank_ifsc_code' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    protected function messages(): array
    {
        return [
            'data.zip.digits_between' => 'Zip Code must be 5 or 6 digits.',
            'data.bank_account_number.digits_between' => 'A/C No must contain only digits.',
            'data.bank_zip.digits_between' => 'Zip Code must be 5 or 6 digits.',
        ];
    }

    /**
     * Human-friendly names for attributes in error messages.
     */
    protected function getValidationAttributes(): array
    {
        return [
            'data.bank_account_number' => 'A/C No',
        ];
    }

    /**
     * Handle form submission: validate, persist, notify, and redirect.
     */
    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        // Validate using the rules() above and the schema-defined attributes.
        $this->validate();

        $data = $this->form->getState();

        $record = ImportCompany::create($data);

        Notification::make()
            ->title('Company created successfully')
            ->success()
            ->send();

        // Redirect to listing page
        $this->redirect(ImportCompanyResource::getUrl('index'));
    }
}
