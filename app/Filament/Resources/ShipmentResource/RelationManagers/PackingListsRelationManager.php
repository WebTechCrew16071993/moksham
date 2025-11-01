<?php

namespace App\Filament\Resources\ShipmentResource\RelationManagers;

use App\Models\PackingList;
use App\Models\Shipment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PackingListsRelationManager extends RelationManager
{
    protected static string $relationship = 'packingLists';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Header block (BKG #, Date, Employee)
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Hidden::make('user_id')
                        ->default(fn () => Auth::id()),
                    Forms\Components\TextInput::make('booking_no')
                        ->label('BKG #')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('From Shipment')
                        ->formatStateUsing(function () {
                            /** @var Shipment $shipment */
                            $shipment = $this->getOwnerRecord();
                            return $shipment?->booking_no;
                        })
                        ->columnSpan(4),
                    Forms\Components\DatePicker::make('date')
                        ->label('Date')
                        ->native(false)
                        ->default(now())
                        ->required()
                        ->columnSpan(4),
                    Forms\Components\TextInput::make('employee')
                        ->default(fn () => Auth::user()?->name)
                        ->required()
                        ->columnSpan(4),
                ]),

                // Shipment / Routing details block
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\TextInput::make('origin')
                        ->placeholder('e.g. CHICAGO, IL')
                        ->default(function () {
                            $shipment = $this->getOwnerRecord();
                            return $shipment?->indent?->origin;
                        })
                        ->columnSpan(6),
                    Forms\Components\TextInput::make('destination')
                        ->placeholder('e.g. HAZIRA, INDIA')
                        ->default(function () {
                            $shipment = $this->getOwnerRecord();
                            return $shipment?->indent?->final_destination;
                        })
                        ->columnSpan(6),
                    Forms\Components\DatePicker::make('ship_date')
                        ->native(false)
                        ->default(function () {
                            $shipment = $this->getOwnerRecord();
                            return $shipment?->booking_date;
                        })
                        ->columnSpan(6),
                    Forms\Components\DatePicker::make('arrival_date')->native(false)->nullable()->columnSpan(6),
                    Forms\Components\TextInput::make('order_no')->nullable()->columnSpan(6),
                    Forms\Components\TextInput::make('ship_in')->placeholder('e.g. 40FT HC CNTR')->nullable()->columnSpan(6),
                    Forms\Components\TextInput::make('contact')
                        ->default(function () {
                            $shipment = $this->getOwnerRecord();
                            return $shipment?->indent?->consignee_name;
                        })
                        ->nullable()->columnSpan(6),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->default(function () {
                            $shipment = $this->getOwnerRecord();
                            return $shipment?->indent?->consignee_phone;
                        })
                        ->nullable()->columnSpan(6),
                ]),

                // Items (containers) table
                Repeater::make('items')
                    ->relationship('items')
                    ->label('Containers')
                    ->addActionLabel('Add Container Row')
                    ->columns(12)
                    ->schema([
                        // Row 1
                        Forms\Components\TextInput::make('container_no')
                            ->label('Container no')
                            ->placeholder('e.g. MRKU5824716')
                            ->required()
                            // Avoid live validation to prevent state resets while editing sibling fields
                            ->dehydrateStateUsing(function ($state) { return strtoupper(trim((string) $state)); })
                            ->extraAttributes(['style' => 'text-transform: uppercase'])
                            ->columnSpan(3),
                        Forms\Components\TextInput::make('seal_no')
                            ->label('Seal no')
                            ->required()
                            ->columnSpan(3),
                        Forms\Components\TextInput::make('description')
                            ->default(function () {
                                $shipment = $this->getOwnerRecord();
                                $indent = $shipment?->indent;
                                return $indent?->hsn_description && $indent?->hsn_code
                                    ? (trim($indent->hsn_description).' HS CODE '.trim($indent->hsn_code))
                                    : null;
                            })
                            ->columnSpan(6),

                        // Row 2
                        Forms\Components\TextInput::make('no_of_bales')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $items = $get('../../items') ?? [];
                                $totalBales = 0;
                                $totalLbs = 0.0;
                                foreach ($items as $row) {
                                    $totalBales += (int) ($row['no_of_bales'] ?? 0);
                                    $totalLbs += (float) ($row['weight_lbs'] ?? 0);
                                }
                                $set('../../../total_bales', $totalBales);
                                $set('../../../total_weight_lbs', $totalLbs);
                                $set('../../../total_weight_kg', round($totalLbs * 0.453592, 3));
                            })
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('weight_lbs')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $lbs = (float) ($get('weight_lbs') ?? 0);
                                $set('weight_kg', round($lbs * 0.453592, 3));
                                $items = $get('../../items') ?? [];
                                $totalBales = 0;
                                $totalLbs = 0.0;
                                foreach ($items as $row) {
                                    $totalBales += (int) ($row['no_of_bales'] ?? 0);
                                    $totalLbs += (float) ($row['weight_lbs'] ?? 0);
                                }
                                $set('../../../total_bales', $totalBales);
                                $set('../../../total_weight_lbs', $totalLbs);
                                $set('../../../total_weight_kg', round($totalLbs * 0.453592, 3));
                            })
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('weight_kg')->numeric()->dehydrated(true)->columnSpan(4),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['container_no'] ?? 'Container')
                    ->columnSpanFull(),

                // Status only (totals & pdf path removed per request)
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'finalized' => 'Finalized',
                        ])->native(false)->required()->columnSpan(6),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('employee')->searchable(),
                Tables\Columns\TextColumn::make('origin')->toggleable(),
                Tables\Columns\TextColumn::make('destination')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'gray' => 'draft',
                    'success' => 'finalized',
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('newPackingList')
                    ->label('Create Packing List')
                    ->icon('heroicon-o-plus')
                    ->url(function () {
                        $shipment = $this->getOwnerRecord();
                        return route('filament.admin.resources.packing-lists.create', ['shipment_id' => $shipment?->getKey()]);
                    })
                    ->openUrlInNewTab(false),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => route('filament.admin.resources.packing-lists.edit', ['record' => $record]))
                    ->openUrlInNewTab(false),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route('packing-lists.generate', ['packingList' => $record->getKey(), 'download' => 0]))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto compute totals from items and convert lbs to kg
        $items = $data['items'] ?? [];
        $totalBales = 0;
        $totalLbs = 0.0;
        foreach ($items as $row) {
            $totalBales += (int) ($row['no_of_bales'] ?? 0);
            $totalLbs += (float) ($row['weight_lbs'] ?? 0);
        }
        $data['total_bales'] = $totalBales;
        $data['total_weight_lbs'] = $totalLbs;
        $data['total_weight_kg'] = round($totalLbs * 0.453592, 3);
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        // Default employee if empty
        if (empty($data['employee']) && Auth::user()) {
            $data['employee'] = Auth::user()->name;
        }
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recompute totals on edit as well
        return $this->mutateFormDataBeforeCreate($data);
    }
}
