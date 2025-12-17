<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditNoteResource\Pages;
use App\Models\CreditDebitNote;
use App\Models\Invoice;
use App\Models\ImportCompany;
use App\Services\DocumentPermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CreditNoteResource extends Resource
{
    protected static ?string $model = CreditDebitNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?string $navigationLabel = 'Credit Notes';
    protected static ?int $navigationSort = 28;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columns(12)->schema([
                Forms\Components\Hidden::make('type')->default('credit'),
                Forms\Components\Select::make('invoice_id')
                    ->label('Against Invoice')
                    ->options(fn () => Invoice::query()->orderByDesc('id')->pluck('invoice_no', 'id'))
                    ->searchable()->preload()->native(false)
                    ->required()->columnSpan(6),
                Forms\Components\Select::make('consignee_id')
                    ->label('Consignee (override)')
                    ->options(fn () => ImportCompany::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()->preload()->native(false)
                    ->columnSpan(6),
                Forms\Components\TextInput::make('note_no')->label('Note No.')
                    ->required()->unique(CreditDebitNote::class, 'note_no', ignoreRecord: true)->columnSpan(3),
                Forms\Components\DatePicker::make('date')->native(false)->required()->columnSpan(3),
                Forms\Components\TextInput::make('heading')->columnSpan(9)->label('Heading / Main Line')->maxLength(255),
            ]),
            Forms\Components\Section::make('Row 1')->columns(12)->schema([
                Forms\Components\Textarea::make('row1_details')->rows(3)->columnSpan(6),
                Forms\Components\TextInput::make('row1_amount_usd')->numeric()->label('Amount (USD)')->columnSpan(3),
                Forms\Components\TextInput::make('row1_amount_credit')->numeric()->label('Amount Credit')->columnSpan(3),
            ]),
            Forms\Components\Section::make('Row 2')
                ->description('Charges incurred or any additional description')
                ->columns(12)
                ->schema([
                    Forms\Components\TextInput::make('subheader')->label('Subheader')->columnSpan(12),
                    Forms\Components\Textarea::make('row2_details')->rows(3)->columnSpan(6),
                    Forms\Components\TextInput::make('row2_amount_usd')->numeric()->label('Amount (USD)')->columnSpan(3),
                    Forms\Components\TextInput::make('row2_amount_credit')->numeric()->label('Amount Credit')->columnSpan(3),
                ]),
            Forms\Components\TextInput::make('total_amount')->numeric()->label('Total Amount')->columnSpan(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('note_no')->label('Note No.')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('date')->date()->sortable(),
            Tables\Columns\TextColumn::make('invoice.invoice_no')->label('Invoice')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('total_amount')->money('usd', true)->sortable(),
            Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('pdf')->label('PDF')->icon('heroicon-o-arrow-down-tray')
                ->url(fn (CreditDebitNote $record) => route('cdn.pdf', ['note' => $record->getKey(), 'download' => 0]))
                ->openUrlInNewTab(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditNotes::route('/'),
            'create' => Pages\CreateCreditNote::route('/create'),
            'edit' => Pages\EditCreditNote::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'credit');
    }

    protected static string $permissionResource = 'invoices';

    public static function shouldRegisterNavigation(): bool
    {
        return DocumentPermissionService::canView(static::$permissionResource);
    }

    public static function canViewAny(): bool
    {
        return DocumentPermissionService::canView(static::$permissionResource);
    }

    public static function canCreate(): bool
    {
        return DocumentPermissionService::canCreate(static::$permissionResource);
    }

    public static function canEdit($record): bool
    {
        return DocumentPermissionService::canUpdate(static::$permissionResource);
    }

    public static function canDelete($record): bool
    {
        return DocumentPermissionService::canDelete(static::$permissionResource);
    }
}
