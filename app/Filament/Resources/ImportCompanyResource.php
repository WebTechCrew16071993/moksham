<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportCompanyResource\Pages;
use App\Models\ImportCompany;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ImportCompanyResource extends Resource
{
    protected static ?string $model = ImportCompany::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Companies';

    protected static ?string $navigationLabel = 'Companies';

    protected static ?int $navigationSort = 20;

    // Use a clean base URL like /admin/companies instead of /admin/import-companies
    public static function getSlug(): string
    {
        return 'companies';
    }

    public static function getModelLabel(): string
    {
        return 'Company';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Companies';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Consignee / Invoice')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Company Name')
                            ->placeholder('e.g. AMBITION PAPER TECH PVT LTD')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('person_name')
                            ->label('Contact Person')
                            ->placeholder('e.g. John Doe')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->label('Address')
                            ->placeholder('Street, Area, City, State, Zip, Country')
                            ->required()
                            ->live(onBlur: true)
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')->placeholder('e.g. Ahmedabad')->required()->live(onBlur: true)->maxLength(255),
                        Forms\Components\TextInput::make('state')->placeholder('e.g. Gujarat')->required()->live(onBlur: true)->maxLength(255),
                        Forms\Components\TextInput::make('zip')
                            ->label('Zip Code')
                            ->placeholder('e.g. 380001')
                            ->required()
                            ->live(onBlur: true)
                            ->rule('digits_between:5,6')
                            ->validationMessages([
                                'digits_between' => 'Zip Code must be 5 or 6 digits.',
                            ])
                            ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*']),
                        Forms\Components\TextInput::make('country')->placeholder('e.g. India')->required()->live(onBlur: true)->maxLength(255),
                        Forms\Components\TextInput::make('iec')->label('IEC')->maxLength(255),
                        Forms\Components\TextInput::make('gstin')->label('GSTIN')->maxLength(255),
                        Forms\Components\TextInput::make('pan')->label('PAN')->maxLength(255),
                        Forms\Components\TextInput::make('email')->label('Email')->email()->required()->live(onBlur: true)->maxLength(255)->rule('email'),
                        Forms\Components\TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->placeholder('+91 98765 43210')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(30)
                            ->rule('regex:/^[0-9+()\\s-]+$/')
                            ->helperText('Digits, spaces, +, -, and () are allowed.'),
                        Forms\Components\Textarea::make('notes')->label('Notes')->rows(2)->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Consignee Bank')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')->label('Bank Name')->required()->live(onBlur: true)->maxLength(255),
                        Forms\Components\Textarea::make('bank_address')->label('Bank Address')->placeholder('Branch, Street, City, State, Zip, Country')->required()->live(onBlur: true)->maxLength(2000)->rows(2)->columnSpanFull(),
                        Forms\Components\TextInput::make('bank_city')->label('City')->required()->live(onBlur: true)->maxLength(255),
                        Forms\Components\TextInput::make('bank_state')->label('State')->required()->live(onBlur: true)->maxLength(255),
                        Forms\Components\TextInput::make('bank_zip')
                            ->label('Zip Code')
                            ->required()
                            ->live(onBlur: true)
                            ->rule('digits_between:5,6')
                            ->validationMessages([
                                'digits_between' => 'Zip Code must be 5 or 6 digits.',
                            ])
                            ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*']),
                        Forms\Components\TextInput::make('bank_country')->label('Country')->required()->live(onBlur: true)->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_number')
                            ->label('A/C No')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(30)
                            ->rule('digits_between:1,30')
                            ->validationMessages([
                                'required' => 'A/C No is required.',
                                'digits_between' => 'A/C No must contain only digits and be between 1 and 30 digits.',
                            ])
                            ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*']),
                        Forms\Components\TextInput::make('bank_swift_code')->label('SWIFT Code')->maxLength(255),
                        Forms\Components\TextInput::make('bank_ifsc_code')->label('IFSC Code')->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('person_name')->label('Contact')->toggleable(),
                Tables\Columns\TextColumn::make('gstin')->label('GSTIN')->toggleable(),
                Tables\Columns\TextColumn::make('iec')->label('IEC')->toggleable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone_number')->label('Phone'),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportCompanies::route('/'),
            'create' => Pages\CreateImportCompany::route('/create'),
            'edit' => Pages\EditImportCompany::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}
