<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\ImportCompany;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Models\BlCorrection;
use App\Services\DocumentPermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Documents';

    protected static ?string $navigationLabel = 'Invoices';

    protected static ?int $navigationSort = 22;

    protected static string $permissionResource = 'invoices';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Hidden::make('packing_list_id'),
                    Forms\Components\Select::make('shipment_id')
                        ->label('Shipment (Booking #)')
                        ->options(fn () => Shipment::query()->orderByDesc('booking_date')->pluck('booking_no', 'id'))
                        ->searchable()->preload()->native(false)
                        ->required()
                        ->columnSpan(6),
                    Forms\Components\Select::make('bl_correction_id')
                        ->label('BL')
                        ->options(function (Get $get) {
                            $shipmentId = $get('shipment_id');
                            $query = BlCorrection::query()->latest('id');
                            if ($shipmentId) {
                                $query->where('shipment_id', $shipmentId);
                            }
                            return $query->take(100)->get()->mapWithKeys(function ($bl) {
                                $label = ($bl->bl_no ? ($bl->bl_no.' - ') : '') . 'ID#'.$bl->id.' · BKG '.($bl->booking_no ?? '-') . ' · '.$bl->destination;
                                return [$bl->id => $label];
                            })->toArray();
                        })
                        ->searchable()
                        ->required()
                        ->preload()
                        ->native(false)
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $bl = $state ? BlCorrection::find($state) : null;
                            if ($bl) {
                                if ($bl->shipment_id) $set('shipment_id', $bl->shipment_id);
                                if ($bl->packing_list_id) $set('packing_list_id', $bl->packing_list_id);
                                if ($bl->booking_no) $set('obl_ref', $bl->booking_no);
                            }
                        })
                        ->columnSpan(6),
                    Forms\Components\TextInput::make('invoice_no')->required()->unique(ignoreRecord: true)->columnSpan(3),
                    Forms\Components\DatePicker::make('date')->native(false)->required()->columnSpan(3),
                    Forms\Components\TextInput::make('employee')->required()->columnSpan(6),
                    Forms\Components\TextInput::make('obl_ref')->label('OBL/Reference')->nullable()->columnSpan(6),
                ]),

                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Select::make('consignee_id')
                        ->label('Bill To (Consignee)')
                        ->options(fn () => ImportCompany::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()->preload()->native(false)
                        ->nullable()->columnSpan(6),
                    Forms\Components\TextInput::make('no_of_cont')->label('No. of Containers')->nullable()->columnSpan(6),
                    Forms\Components\TextInput::make('weight_mt')->numeric()->label('Weight (MT)')->nullable()->columnSpan(4),
                    Forms\Components\TextInput::make('rate_mt')->numeric()->label('Rate / MT (US$)')->nullable()->columnSpan(4),
                    Forms\Components\TextInput::make('amount')->numeric()->label('Amount (US$)')->nullable()->columnSpan(4),
                    Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
                    Forms\Components\TextInput::make('moisture_contain')->label('Moisture Contain')->nullable()->columnSpan(6),
                    Forms\Components\TextInput::make('payment_terms')->nullable()->columnSpan(6),
                    Forms\Components\TextInput::make('notify')->label('Notify')->nullable()->columnSpan(6),
                    Forms\Components\TextInput::make('quotation')->label('Quotation')
                        // ->helperText(admin@moksham.comadmin@moksham.com'Optional reference that appears on PDF')
                        ->nullable()->columnSpan(6),
                ]),

                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\DatePicker::make('return_date')->native(false)->nullable()->columnSpan(4),
                    Forms\Components\DatePicker::make('cut_off_date')->native(false)->nullable()->columnSpan(4),
                    Forms\Components\DatePicker::make('dept_est')->native(false)->label('Departure (Est)')->nullable()->columnSpan(4),
                    Forms\Components\DatePicker::make('arrival')->native(false)->nullable()->columnSpan(4),
                    Forms\Components\DatePicker::make('si_cut_off')->native(false)->nullable()->columnSpan(4),
                    Forms\Components\TextInput::make('origin')->label('Origin')->nullable()->columnSpan(4),
                    Forms\Components\TextInput::make('f_dest')->label('Final Destination')->nullable()->columnSpan(4),
                ]),

                // Read-only context from Shipment and Packing List (non-collapsible to avoid method issues)
                Forms\Components\Section::make('Context')
                    ->columns(12)
                    ->schema([
                    Forms\Components\TextInput::make('__booking_no')
                        ->label('Booking No.')
                        ->disabled()->dehydrated(false)->columnSpan(3)
                        ->formatStateUsing(function ($state, Get $get) {
                            $shipmentId = $get('shipment_id');
                            $shipment = $shipmentId ? Shipment::find($shipmentId) : null;
                            return $shipment?->booking_no;
                        }),
                    Forms\Components\TextInput::make('__carrier')
                        ->label('Carrier')
                        ->disabled()->dehydrated(false)->columnSpan(3)
                        ->formatStateUsing(function ($state, Get $get) {
                            $shipmentId = $get('shipment_id');
                            $shipment = $shipmentId ? Shipment::find($shipmentId) : null;
                            return $shipment?->carrier;
                        }),
                    Forms\Components\TextInput::make('__vessel')
                        ->label('Vessel')
                        ->disabled()->dehydrated(false)->columnSpan(3)
                        ->formatStateUsing(function ($state, Get $get) {
                            $shipmentId = $get('shipment_id');
                            $shipment = $shipmentId ? Shipment::find($shipmentId) : null;
                            return $shipment?->vessel;
                        }),
                    Forms\Components\TextInput::make('__contact')
                        ->label('Contact')
                        ->disabled()->dehydrated(false)->columnSpan(3)
                        ->formatStateUsing(function ($state, Get $get) {
                            // Try to use packing_list_id from state if present
                            $packingListId = $get('packing_list_id');
                            if ($packingListId) {
                                $pl = \App\Models\PackingList::find($packingListId);
                                return $pl?->contact;
                            }
                            // Fallback: infer via shipment if needed
                            $shipmentId = $get('shipment_id');
                            if ($shipmentId) {
                                $pl = \App\Models\PackingList::where('shipment_id', $shipmentId)->latest()->first();
                                return $pl?->contact;
                            }
                            return null;
                        }),
                    Forms\Components\TextInput::make('__phone')
                        ->label('Phone')
                        ->disabled()->dehydrated(false)->columnSpan(3)
                        ->formatStateUsing(function ($state, Get $get) {
                            $packingListId = $get('packing_list_id');
                            if ($packingListId) {
                                $pl = \App\Models\PackingList::find($packingListId);
                                return $pl?->phone;
                            }
                            $shipmentId = $get('shipment_id');
                            if ($shipmentId) {
                                $pl = \App\Models\PackingList::where('shipment_id', $shipmentId)->latest()->first();
                                return $pl?->phone;
                            }
                            return null;
                        }),
                ]),

                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\TextInput::make('advance')->numeric()->default(0)->columnSpan(4),
                    Forms\Components\TextInput::make('amount_due')->numeric()->nullable()->columnSpan(4),
                    Forms\Components\Select::make('status')->options([
                        'draft' => 'Draft',
                        'finalized' => 'Finalized',
                        'sent' => 'Sent',
                        'paid' => 'Paid',
                    ])->native(false)->required()->columnSpan(4),
                ]),

                Forms\Components\RichEditor::make('remarks')->columnSpanFull()->nullable(),
                Forms\Components\RichEditor::make('terms_conditions')->label('Terms & Conditions')->columnSpanFull()->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('shipment.booking_no')->label('BKG #')->searchable(),
                Tables\Columns\TextColumn::make('blCorrection.id')
                    ->label('BL #')
                    ->formatStateUsing(fn($state, Invoice $record) => $record->blCorrection?->bl_no ?? $record->blCorrection?->id)
                    ->url(fn (Invoice $record) => $record->blCorrection ? \App\Filament\Resources\BlCorrectionResource::getUrl('edit', ['record' => $record->blCorrection->getKey()]) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('consignee.name')->label('Bill To')->searchable(),
                Tables\Columns\TextColumn::make('amount')->money('usd', true)->sortable(),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'gray' => 'draft',
                    'info' => 'finalized',
                    'warning' => 'sent',
                    'success' => 'paid',
                ])->sortable(),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'finalized' => 'Finalized',
                        'sent' => 'Sent',
                        'paid' => 'Paid',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Invoice $record) => route('invoices.pdf', ['invoice' => $record->getKey(), 'download' => 0]))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make()
                    ->visible(function (Invoice $record) {
                        $hasForm6 = \App\Models\Form6Document::query()->where('invoice_id', $record->getKey())->exists();
                        $hasForm9 = \App\Models\Form9Document::query()->where('invoice_id', $record->getKey())->exists();
                        return !($hasForm6 || $hasForm9);
                    }),
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
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
