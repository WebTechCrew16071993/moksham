<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillOfExchangeResource\Pages;
use App\Models\BillOfExchange;
use App\Models\Invoice;
use App\Services\DocumentPermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BillOfExchangeResource extends Resource
{
    protected static ?string $model = BillOfExchange::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?string $navigationLabel = 'Bill of Exchange';
    protected static ?int $navigationSort = 26;

    protected static string $permissionResource = 'boe';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columns(12)->schema([
                Forms\Components\Select::make('invoice_id')
                    ->label('Invoice')
                    ->options(fn()=> Invoice::query()->latest('id')->pluck('invoice_no','id'))
                    ->searchable()->preload()->native(false)->required()->columnSpan(4),
                Forms\Components\TextInput::make('ref_no')->label('Ref No')->readOnly()->columnSpan(4),
                Forms\Components\DatePicker::make('issue_date')->label('Issue Date')->native(false)->columnSpan(4),
                Forms\Components\TextInput::make('place_of_issue')->label('Place')->columnSpan(4),
                Forms\Components\TextInput::make('amount_usd')->numeric()->columnSpan(4),
                Forms\Components\TextInput::make('amount_in_words')->columnSpan(12),
                Forms\Components\Select::make('status')->options([
                    'draft'=>'Draft','finalized'=>'Finalized'
                ])->default('draft')->native(false)->columnSpan(4),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('ref_no')->label('Ref No')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('issue_date')->label('Date')->date()->sortable(),
            Tables\Columns\TextColumn::make('invoice.invoice_no')->label('Invoice')->searchable(),
            Tables\Columns\TextColumn::make('amount_usd')->money('usd', true)->sortable(),
            Tables\Columns\BadgeColumn::make('status')->colors([
                'gray'=>'draft','success'=>'finalized'
            ])->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn(BillOfExchange $record) => route('boe.pdf', ['boe'=>$record->getKey(),'download'=>0]))
                ->openUrlInNewTab(),
            // Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillOfExchanges::route('/'),
            'create' => Pages\CreateBillOfExchange::route('/create'),
            'edit' => Pages\EditBillOfExchange::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return DocumentPermissionService::canView(static::$permissionResource);
    }

    public static function canViewAny(): bool { return DocumentPermissionService::canView(static::$permissionResource); }
    public static function canCreate(): bool { return DocumentPermissionService::canCreate(static::$permissionResource); }
    public static function canEdit($record): bool { return DocumentPermissionService::canUpdate(static::$permissionResource); }
    public static function canDelete($record): bool { return DocumentPermissionService::canDelete(static::$permissionResource); }
}
