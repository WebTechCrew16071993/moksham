<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentaryCollectionLetterResource\Pages;
use App\Models\DocumentaryCollectionLetter;
use App\Models\Invoice;
use App\Services\DocumentPermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentaryCollectionLetterResource extends Resource
{
    protected static ?string $model = DocumentaryCollectionLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?string $navigationLabel = 'Documentary Collection Letter';
    protected static ?int $navigationSort = 25;

    protected static string $permissionResource = 'dcl';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columns(12)->schema([
                Forms\Components\Select::make('invoice_id')
                    ->label('Invoice')
                    ->options(fn()=> Invoice::query()->latest('id')->pluck('invoice_no','id'))
                    ->searchable()->preload()->native(false)->required()->columnSpan(4),
                Forms\Components\TextInput::make('letter_no')->label('No')->columnSpan(4),
                Forms\Components\DatePicker::make('letter_date')->label('Date')->native(false)->columnSpan(4),
                Forms\Components\TextInput::make('sa_contract_no')->label("SA's Contract No.")->readOnly()->columnSpan(4),
                Forms\Components\TextInput::make('amount_usd')->numeric()->columnSpan(4),
                Forms\Components\Textarea::make('notes')->rows(2)->columnSpan(12),
                Forms\Components\TextInput::make('signer_name')->columnSpan(6),
                Forms\Components\TextInput::make('signer_title')->columnSpan(6),
                Forms\Components\Select::make('status')->options([
                    'draft'=>'Draft','finalized'=>'Finalized'
                ])->default('draft')->native(false)->columnSpan(4),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('letter_no')->label('No')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('letter_date')->label('Date')->date()->sortable(),
            Tables\Columns\TextColumn::make('sa_contract_no')->label("SA's Contract"),
            Tables\Columns\TextColumn::make('invoice.invoice_no')->label('Invoice')->searchable(),
            Tables\Columns\BadgeColumn::make('status')->colors([
                'gray'=>'draft','success'=>'finalized'
            ])->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn(DocumentaryCollectionLetter $record) => route('dcl.pdf', ['letter'=>$record->getKey(),'download'=>0]))
                ->openUrlInNewTab(),
            // Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentaryCollectionLetters::route('/'),
            'create' => Pages\CreateDocumentaryCollectionLetter::route('/create'),
            'edit' => Pages\EditDocumentaryCollectionLetter::route('/{record}/edit'),
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
