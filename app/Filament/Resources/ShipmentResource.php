<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentResource\Pages;
use App\Filament\Resources\ShipmentResource\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\ShipmentResource\RelationManagers\PackingListsRelationManager;
use App\Filament\Resources\ShipmentResource\RelationManagers\BlCorrectionsRelationManager;
use App\Models\Indent;
use App\Models\Shipment;
use App\Services\DocumentPermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Documents';

    protected static ?string $navigationLabel = 'Shipments';

    protected static ?int $navigationSort = 20;

    protected static string $permissionResource = 'shipments';

    public static function getModelLabel(): string
    {
        return 'Shipment';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Shipments';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->columns(12)
                    ->schema([
                        Forms\Components\Select::make('indent_id')
                            ->label('Indent')
                            ->options(fn () => Indent::query()->orderByDesc('indent_no')->pluck('indent_no', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->columnSpan(12),
                        Forms\Components\TextInput::make('booking_no')
                            ->label('Booking No')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(12),
                        Forms\Components\DatePicker::make('booking_date')
                            ->label('Booking Date')
                            ->native(false)
                            ->required()
                            ->columnSpan(12),
                    ]),

                Forms\Components\Group::make()
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('carrier')
                            ->placeholder('e.g. MAERSK')
                            ->columnSpan(12),
                        Forms\Components\TextInput::make('vessel')
                            ->placeholder('e.g. MAERSK SELETAR')
                            ->columnSpan(12),
                    ]),

                Forms\Components\Section::make('Shipment Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'confirmed' => 'Confirmed',
                                'in_transit' => 'In Transit',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->native(false)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_no')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('booking_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('indent.indent_no')->label('Indent No')->sortable(),
                Tables\Columns\TextColumn::make('carrier')->toggleable(),
                Tables\Columns\TextColumn::make('vessel')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'confirmed',
                        'warning' => 'in_transit',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'confirmed' => 'Confirmed',
                        'in_transit' => 'In Transit',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(function (\App\Models\Shipment $record) {
                        $hasPackingList = \App\Models\PackingList::query()->where('shipment_id', $record->getKey())->exists();
                        $hasBl          = \App\Models\BlCorrection::query()->where('shipment_id', $record->getKey())->exists();
                        $hasInvoice     = \App\Models\Invoice::query()->where('shipment_id', $record->getKey())->exists();
                        return !($hasPackingList || $hasBl || $hasInvoice);
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PackingListsRelationManager::class,
            BlCorrectionsRelationManager::class,
            InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShipments::route('/'),
            'create' => Pages\CreateShipment::route('/create'),
            'edit' => Pages\EditShipment::route('/{record}/edit'),
        ];
    }

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
