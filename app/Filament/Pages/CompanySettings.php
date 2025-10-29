<?php

namespace App\Filament\Pages;

use App\Models\CompanySetting;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;

class CompanySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Company Settings';

    protected static ?string $title = 'Company Settings';

    protected static ?string $navigationGroup = 'Setting';

    protected static ?int $navigationSort = 60;

    protected static string $view = 'filament.pages.company-settings';

    public ?array $data = [];

    public ?CompanySetting $record = null;

    public function mount(): void
    {
        $this->record = CompanySetting::query()->first();
        if ($this->record) {
            $this->form->fill($this->record->attributesToArray());
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Company Information')
                    ->description('Your company details that will appear on shipping documents')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Company Name')
                            ->placeholder('MOKSHAM EXPORT IMPORT LLC')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('company_address')
                            ->label('Address')
                            ->placeholder('7511 Barkstone Lane')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('company_city')
                            ->label('City')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_state')
                            ->label('State')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_zip')
                            ->label('Zip Code')
                            ->placeholder('77469')
                            ->required()
                            ->maxLength(20)
                            ->rule('max:20'),
                        Forms\Components\TextInput::make('company_country')
                            ->label('Country')
                            ->placeholder('USA')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_phone')
                            ->label('Phone')
                            ->tel()
                            ->placeholder('+1 241 232 321')
                            ->required()
                            ->maxLength(30)
                            ->rule('regex:/^[0-9+()\\s-]+$/')
                            ->helperText('Digits, spaces, +, -, and () are allowed.'),
                        Forms\Components\TextInput::make('company_email')
                            ->label('Email')
                            ->email()
                            ->placeholder('info@moksham.com')
                            ->required()
                            ->maxLength(255)
                            ->rule('email'),
                    ])->columns(2),

                Section::make('Bank Information')
                    ->description('Banking details for shipping and payment documents')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')->label('Bank Name')->required()->maxLength(255),
                        Forms\Components\Textarea::make('bank_address')->label('Bank Address')->required()->rows(2)->columnSpanFull(),
                        Forms\Components\TextInput::make('bank_city')->label('City')->required()->maxLength(255),
                        Forms\Components\TextInput::make('bank_state')->label('State')->required()->maxLength(255),
                        Forms\Components\TextInput::make('bank_zip')->label('Zip Code')->required()->maxLength(20)->rule('max:20'),
                        Forms\Components\TextInput::make('bank_country')->label('Country')->required()->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_number')->label('Account Number')->required()->maxLength(255),
                        Forms\Components\TextInput::make('bank_swift_code')->label('SWIFT Code')->maxLength(255),
                        Forms\Components\TextInput::make('bank_routing_number')->label('Routing Number')->maxLength(255),
                    ])->columns(2),

                Section::make('Tax & Registration Information')
                    ->description('Company registration and tax details')
                    ->schema([
                        Forms\Components\TextInput::make('tax_iec_number')->label('IEC Number')->maxLength(255),
                        Forms\Components\TextInput::make('tax_gstin')->label('GSTIN')->maxLength(255),
                        Forms\Components\TextInput::make('tax_pan_number')->label('PAN Number')->maxLength(255),
                    ])->columns(3),

                Section::make('Company Signature')
                    ->schema([
                        Forms\Components\FileUpload::make('company_signed_logo')
                            ->label('Company Signed Logo')
                            ->image()
                            ->imageEditor()
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/png','image/jpeg','image/webp'])
                            ->required(false)
                            ->disk('public')
                            ->directory('company')
                            ->visibility('public')
                            ->imagePreviewHeight('150')
                            ->helperText('PNG, JPG or WebP. Max 2MB.' )
                            ->openable(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $validated = $this->form->getState();

        if ($this->record) {
            $this->record->update($validated);
        } else {
            $this->record = CompanySetting::create($validated);
        }

        Notification::make()
            ->title('Company settings saved')
            ->success()
            ->send();
    }

    /**
     * Reset the form state back to the saved record values (or defaults if none).
     */
    public function resetToSaved(): void
    {
        // Get the saved record data or empty array if no record exists
        $data = $this->record?->attributesToArray() ?? [];
        
        // Reset the form to the saved values
        $this->form->fill($data);
        
        // Show notification
        Notification::make()
            ->title('Form reset to saved values')
            ->info()
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}
