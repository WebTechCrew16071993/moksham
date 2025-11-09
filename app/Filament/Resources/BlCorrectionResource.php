<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlCorrectionResource\Pages;
use App\Models\BlCorrection;
use App\Models\Shipment;
use App\Models\PackingList;
use App\Services\InvoiceGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class BlCorrectionResource extends Resource
{
    protected static ?string $model = BlCorrection::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?string $navigationLabel = 'BLs';
    protected static ?int $navigationSort = 22;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Reference')->columns(12)->schema([
                Forms\Components\Select::make('shipment_id')
                    ->label('Shipment')
                    ->options(fn() => Shipment::query()->orderByDesc('booking_date')->pluck('booking_no', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        $shipment = $state ? Shipment::with('packingLists')->find($state) : null;
                        $set('booking_no', $shipment?->booking_no);
                    })
                    ->columnSpan(4),
                Forms\Components\Select::make('packing_list_id')
                    ->label('Packing List')
                    ->options(function (Forms\Get $get) {
                        $shipmentId = $get('shipment_id');
                        return $shipmentId
                            ? PackingList::where('shipment_id', $shipmentId)->orderByDesc('id')->pluck('id', 'id')
                            : [];
                    })
                    ->required()
                    ->searchable()
                    ->columnSpan(4),
                Forms\Components\TextInput::make('booking_no')->disabled()->dehydrated(false)->columnSpan(4),
                Forms\Components\DatePicker::make('bl_date')->native(false)->required()->columnSpan(4),
            ]),

            Section::make('Shipper & Parties')->schema([
                // Shipper block
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Textarea::make('shipper_details')
                        ->label('Shipper details')
                        ->rows(4)
                        ->columnSpan(6),
                    Forms\Components\Group::make()->columns(12)->columnSpan(6)->schema([
                        Forms\Components\TextInput::make('shipper_phone')->label('Shipper phone')->columnSpan(6),
                        Forms\Components\TextInput::make('shipper_email')->label('Shipper email')->email()->columnSpan(6),
                    ]),
                ]),

                // Consignee block
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Textarea::make('consignee_details')
                        ->label("Consignee details")
                        ->rows(4)
                        ->columnSpan(6),
                    Forms\Components\Group::make()->columns(12)->columnSpan(6)->schema([
                        Forms\Components\TextInput::make('consignee_iec')->label('Consignee iec')->columnSpan(6),
                        Forms\Components\TextInput::make('consignee_gstin')->label('Consignee gstin')->columnSpan(6),
                        Forms\Components\TextInput::make('consignee_pan')->label('Consignee pan')->columnSpan(6),
                        Forms\Components\TextInput::make('consignee_email')->label('Consignee email')->email()->columnSpan(6),
                    ]),
                ]),

                // Notify Party block
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Textarea::make('notify_party_details')
                        ->label('Notify party details')
                        ->rows(4)
                        ->columnSpan(6),
                    Forms\Components\Group::make()->columns(12)->columnSpan(6)->schema([
                        Forms\Components\TextInput::make('notify_party_iec')->label('Notify party iec')->columnSpan(6),
                        Forms\Components\TextInput::make('notify_party_gstin')->label('Notify party gstin')->columnSpan(6),
                        Forms\Components\TextInput::make('notify_party_pan')->label('Notify party pan')->columnSpan(6),
                        Forms\Components\TextInput::make('notify_party_email')->label('Notify party email')->email()->columnSpan(6),
                    ]),
                ]),
            ]),

            Section::make('Ports & Cargo')->columns(12)->schema([
                Forms\Components\TextInput::make('port_of_loading')->required()->columnSpan(4),
                Forms\Components\TextInput::make('origin')->required()->columnSpan(4),
                Forms\Components\TextInput::make('destination')->required()->columnSpan(4),
                Forms\Components\TextInput::make('net_weight_kgs')
                    ->label('Net weight (kgs)')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $kg = (float) $state;
                        $set('net_weight_mts', $kg > 0 ? round($kg / 1000, 3) : null);
                        $set('net_weight_lbs', $kg > 0 ? round($kg / 0.453592, 3) : null);
                    })
                    ->columnSpan(4),
                Forms\Components\TextInput::make('net_weight_mts')
                    ->label('Net weight (mts)')
                    ->numeric()
                    ->required()
                    ->columnSpan(4),
                Forms\Components\TextInput::make('net_weight_lbs')
                    ->label('Net weight (lbs)')
                    ->disabled()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Set $set, $state, Get $get) {
                        $kg = (float) $get('net_weight_kgs');
                        $set('net_weight_lbs', $kg > 0 ? round($kg / 0.453592, 3) : null);
                    })
                    ->columnSpan(4),
                Forms\Components\TextInput::make('cargo_value')->numeric()->required()->columnSpan(4),
                Forms\Components\TextInput::make('currency')->default('USD')->maxLength(3)->columnSpan(2),
                Forms\Components\TextInput::make('packaging_type')->columnSpan(5),
                Forms\Components\TextInput::make('ship_in')->columnSpan(5),
                Forms\Components\TextInput::make('no_of_containers')->numeric()->required()->columnSpan(3),
                Forms\Components\TextInput::make('container_type')->columnSpan(3),
                Forms\Components\TextInput::make('document_type')->default('OBL')->columnSpan(3),
                Forms\Components\TextInput::make('total_bales')->numeric()->required()->columnSpan(3),
                Forms\Components\TextInput::make('hs_code')->columnSpan(6),
                Forms\Components\Textarea::make('commodity_description')->rows(2)->columnSpan(6),
            ]),

            Section::make('Containers')->schema([
                Repeater::make('items')
                    ->relationship('items')
                    ->addActionLabel('Add Container')
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('container_no')->required()->columnSpan(3),
                        Forms\Components\TextInput::make('seal_no')->required()->columnSpan(3),
                        Forms\Components\TextInput::make('commodity')->required()->columnSpan(3),
                        Forms\Components\TextInput::make('hs_code')->columnSpan(3),
                        Forms\Components\TextInput::make('no_of_bales')->numeric()->required()->columnSpan(3),
                        Forms\Components\TextInput::make('weight_kgs')
                            ->label('Weight kgs')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $kg = (float) $state;
                                $set('weight_lbs', $kg > 0 ? round($kg / 0.453592, 3) : null);
                            })
                            ->columnSpan(3),
                        Forms\Components\TextInput::make('weight_lbs')
                            ->label('Weight lbs')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Set $set, $state, Get $get) {
                                $kg = (float) $get('weight_kgs');
                                $set('weight_lbs', $kg > 0 ? round($kg / 0.453592, 3) : null);
                            })
                            ->columnSpan(3),
                        Forms\Components\Textarea::make('correction_notes')->rows(1)->columnSpan(6),
                    ])
                    ->reorderable(false)
                    ->collapsed(false)
                    ->columnSpanFull(),
            ]),

            Section::make('Workflow')->columns(12)->schema([
                Forms\Components\Select::make('status')->options([
                    'draft' => 'Draft',
                    'pending_review' => 'Pending Review',
                    'corrections_requested' => 'Corrections Requested',
                    'approved' => 'Approved',
                    'finalized' => 'Finalized',
                ])->native(false)->required()->columnSpan(4),
                Forms\Components\DateTimePicker::make('sent_to_buyer_at')->native(false)->columnSpan(4),
                Forms\Components\DateTimePicker::make('buyer_reviewed_at')->native(false)->columnSpan(4),
                Forms\Components\Textarea::make('buyer_comments')->rows(2)->columnSpan(12),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('BL #')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('shipment.booking_no')->label('BKG #')->searchable(),
            Tables\Columns\TextColumn::make('packing_list_id')->label('PL #'),
            // Tables\Columns\TextColumn::make('bl_no')->label('BL No')->toggleable(),
            Tables\Columns\TextColumn::make('bl_date')->date(),
            Tables\Columns\TextColumn::make('destination')->searchable(),
            Tables\Columns\TextColumn::make('no_of_containers')->label('# Cntrs'),
            Tables\Columns\BadgeColumn::make('status')->colors([
                'gray' => 'draft',
                'warning' => 'pending_review',
                'danger' => 'corrections_requested',
                'success' => 'approved',
                'primary' => 'finalized',
            ])->sortable(),
            Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn(BlCorrection $record) => route('bls.pdf', ['bl' => $record->getKey(), 'download' => 0]))
                ->openUrlInNewTab(),
            Tables\Actions\Action::make('sendToBuyer')
                ->label('Send for Review')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn(BlCorrection $record) => $record->status === 'draft' || $record->status === 'corrections_requested')
                ->requiresConfirmation()
                ->action(function (BlCorrection $record) {
                    $record->update([
                        'status' => 'pending_review',
                        'sent_to_buyer_at' => now(),
                    ]);
                }),
            Tables\Actions\Action::make('markCorrections')
                ->label('Corrections Requested')
                ->icon('heroicon-o-pencil-square')
                ->visible(fn(BlCorrection $record) => $record->status === 'pending_review')
                ->requiresConfirmation()
                ->action(function (BlCorrection $record) {
                    $record->update([
                        'status' => 'corrections_requested',
                        'buyer_reviewed_at' => now(),
                    ]);
                }),
            Tables\Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->visible(fn(BlCorrection $record) => in_array($record->status, ['pending_review', 'corrections_requested']))
                ->requiresConfirmation()
                ->action(function (BlCorrection $record) {
                    $record->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => Auth::id(),
                    ]);

                    // Auto-generate invoice on approval with robust fallbacks
                    try {
                        $record->loadMissing('packingList');
                        $packingList = $record->packingList;
                        if (!$packingList) {
                            $packingList = \App\Models\PackingList::where('shipment_id', $record->shipment_id)
                                ->latest('id')->first();
                        }
                        if (!$packingList) {
                            Notification::make()
                                ->title('Packing List not found')
                                ->body('Cannot generate invoice because no Packing List is associated with this BL or shipment.')
                                ->danger()
                                ->send();
                            return; // Stay on the same page
                        }

                        $generator = app(InvoiceGenerator::class);
                        $invoice = $generator->generateOrUpdateForPackingList($packingList);
                        Notification::make()
                            ->title('Invoice generated')
                            ->body('Invoice #' . $invoice->invoice_no . ' has been generated.')
                            ->success()
                            ->send();
                        return; // stay on the table
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Invoice generation failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        return; // Do not redirect on error
                    }
                }),
            Tables\Actions\Action::make('finalize')
                ->label('Finalize')
                ->icon('heroicon-o-check')
                ->visible(fn(BlCorrection $record) => $record->status === 'approved')
                ->requiresConfirmation()
                ->action(function (BlCorrection $record) {
                    $record->update(['status' => 'finalized']);
                }),
            Tables\Actions\Action::make('generateInvoice')
                ->label('Generate Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->visible(fn(BlCorrection $record) => in_array($record->status, ['approved', 'finalized']))
                ->requiresConfirmation()
                ->action(function (BlCorrection $record) {
                    $generator = app(InvoiceGenerator::class);
                    // Generate from related packing list for consistency
                    $invoice = $generator->generateOrUpdateForPackingList($record->packingList);
                    return redirect()->route('invoices.pdf', ['invoice' => $invoice->getKey(), 'download' => 0]);
                }),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlCorrections::route('/'),
            'create' => Pages\CreateBlCorrection::route('/create'),
            'edit' => Pages\EditBlCorrection::route('/{record}/edit'),
        ];
    }
}
