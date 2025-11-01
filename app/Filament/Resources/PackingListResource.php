<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackingListResource\Pages;
use App\Models\PackingList;
use App\Models\Shipment;
use App\Services\InvoiceGenerator;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PackingListResource extends Resource
{
    protected static ?string $model = PackingList::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?string $navigationLabel = 'Packing Lists';
    protected static ?int $navigationSort = 21;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('user_id')->default(fn() => Auth::id()),

            Forms\Components\Select::make('shipment_id')
                ->label('Shipment')
                ->options(fn() => Shipment::query()
                    ->whereNotNull('booking_no')
                    ->where('booking_no', '!=', '0')
                    ->orderByDesc('booking_date')
                    ->pluck('booking_no', 'id'))
                ->placeholder('Select shipment')
                ->searchable()
                ->preload()
                ->native(false)
                ->default(fn() => request()->has('shipment_id') ? (int) request('shipment_id') : null)
                ->required()
                ->reactive()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $shipment = $state ? Shipment::with('indent')->find($state) : null;
                    $set('booking_no', $shipment?->booking_no);
                    $set('date', now());
                    $set('origin', $shipment?->indent?->origin);
                    $set('destination', $shipment?->indent?->final_destination);
                    $set('ship_date', $shipment?->booking_date);
                    $set('contact', $shipment?->indent?->consignee_name);
                    $set('phone', $shipment?->indent?->consignee_phone);
                })
                ->afterStateHydrated(function (Set $set, $state, $record) {
                    if ($record && $record->shipment) {
                        $set('booking_no', $record->shipment->booking_no);
                    }
                }),

            Forms\Components\Group::make()->columns(12)->schema([
                Forms\Components\TextInput::make('booking_no')
                    ->label('BKG #')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn($state, $record) => $record?->shipment?->booking_no ?? $state)
                    ->columnSpan(4),
                Forms\Components\DatePicker::make('date')->native(false)->required()->columnSpan(4),
                Forms\Components\TextInput::make('employee')->default(fn() => Auth::user()?->name)->required()->columnSpan(4),
            ]),

            Forms\Components\Group::make()->columns(12)->schema([
                Forms\Components\TextInput::make('origin')->columnSpan(6),
                Forms\Components\TextInput::make('destination')->columnSpan(6),
                Forms\Components\DatePicker::make('ship_date')->native(false)->columnSpan(6),
                Forms\Components\DatePicker::make('arrival_date')->native(false)->nullable()->columnSpan(6),
                Forms\Components\TextInput::make('order_no')->nullable()->columnSpan(6),
                Forms\Components\TextInput::make('ship_in')
                    ->placeholder('e.g. 40FT HC CNTR')
                    ->nullable()
                    ->reactive()
                    ->columnSpan(6),

                // ✅ Editable but auto-filled if left empty
                Forms\Components\TextInput::make('container_no_summary')
                    ->label('Containers')
                    ->placeholder('Auto-filled if left empty')
                    ->helperText('If left blank, it will auto-fill from container count and ship type.')
                    ->dehydrated(true)
                    ->columnSpan(6),
                Forms\Components\TextInput::make('contact')->nullable()->columnSpan(6),
                Forms\Components\TextInput::make('phone')->tel()->nullable()->columnSpan(6),
            ]),

            Repeater::make('items')
                ->relationship('items', modifyQueryUsing: fn($query) => $query->orderBy('id'))
                ->label('Containers')
                ->addActionLabel('Add Container Row')
                ->columns(12)
                ->defaultItems(1)
                ->collapsed(false)
                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                    $shipIn = strtoupper((string) $get('ship_in'));
                    $count = is_array($state) ? count($state) : 0;
                    // Only update if the field is currently empty
                    if (empty($get('container_no_summary')) && $count > 0 && $shipIn) {
                        $set('container_no_summary', $count . ' x ' . $shipIn);
                    }
                })
                ->schema([
                    Forms\Components\TextInput::make('container_no')
                        ->label('Container no')
                        ->placeholder('e.g. MRKU5824716')
                        ->required()
                        ->afterStateHydrated(fn(Set $set, $state) => $set('container_no', strtoupper((string) $state)))
                        ->dehydrateStateUsing(fn($state) => strtoupper(trim((string) $state)))
                        ->extraAttributes(['style' => 'text-transform: uppercase'])
                        ->columnSpan(3),
                    Forms\Components\TextInput::make('seal_no')->label('Seal no')->required()->columnSpan(3),
                    Forms\Components\TextInput::make('description')
                        ->default(function (Get $get) {
                            $shipmentId = $get('../../shipment_id');
                            $shipment = $shipmentId ? Shipment::with('indent')->find($shipmentId) : null;
                            $indent = $shipment?->indent;
                            return $indent?->hsn_description && $indent?->hsn_code
                                ? (trim($indent->hsn_description) . ' HS CODE ' . trim($indent->hsn_code))
                                : null;
                        })
                        ->columnSpan(6),
                    Forms\Components\TextInput::make('no_of_bales')->numeric()->required()->columnSpan(4),
                    Forms\Components\TextInput::make('weight_lbs')
                    ->numeric()
                    ->label('Weight (lbs)')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('weight_kg', round($state * 0.453592, 3));
                    })->columnSpan(4),
                Forms\Components\TextInput::make('weight_kg')
                    ->numeric()
                    ->label('Weight (kg)')
                    ->dehydrated(true)->columnSpan(4),
                ])
                ->reorderable(false)
                ->itemLabel(fn(array $state): ?string => 'Container')
                ->columnSpanFull(),

            Forms\Components\Group::make()->columns(12)->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'finalized' => 'Finalized',
                    ])
                    ->native(false)
                    ->required()
                    ->columnSpan(6),
            ]),

            Forms\Components\KeyValue::make('__totals')->hidden()->dehydrated(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shipment.booking_no')->label('BKG #')->searchable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('employee')->searchable(),
                Tables\Columns\TextColumn::make('origin')->toggleable(),
                Tables\Columns\TextColumn::make('destination')->toggleable(),
                Tables\Columns\TextColumn::make('container_no_summary')
                    ->label('Containers')
                    ->formatStateUsing(function ($state, $record) {
                        if (!empty($state)) return $state;
                        $count = $record->items()->count();
                        $shipIn = strtoupper((string) $record->ship_in);
                        return $count > 0 && $shipIn ? ($count . ' x ' . $shipIn) : null;
                    })
                    ->sortable(false),
                Tables\Columns\TextColumn::make('first_container')
                    ->label('First container')
                    ->getStateUsing(fn($record) => optional($record->items()->orderBy('id')->first())->container_no),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('# Containers')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'gray' => 'draft',
                    'success' => 'finalized',
                ])->sortable(),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('generateInvoice')
                    ->label('Generate Invoice')
                    ->icon('heroicon-o-document-currency-dollar')
                    ->visible(fn(PackingList $record) => !$record->shipment?->invoices()->exists())
                    ->requiresConfirmation()
                    ->action(function (PackingList $record) {
                        $generator = app(InvoiceGenerator::class);
                        $invoice = $generator->generateOrUpdateForPackingList($record);
                        return redirect()->route('invoices.pdf', ['invoice' => $invoice->getKey(), 'download' => 0]);
                    }),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(PackingList $record) => route('packing-lists.generate', ['packingList' => $record->getKey(), 'download' => 0]))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackingLists::route('/'),
            'create' => Pages\CreatePackingList::route('/create'),
            'edit' => Pages\EditPackingList::route('/{record}/edit'),
        ];
    }
}
