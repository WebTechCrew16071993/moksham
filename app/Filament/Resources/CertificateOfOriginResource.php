<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateOfOriginResource\Pages;
use App\Models\CertificateOfOrigin;
use App\Models\Shipment;
use App\Models\PackingList;
use App\Filament\Resources\PackingListResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class CertificateOfOriginResource extends Resource
{
    protected static ?string $model = CertificateOfOrigin::class;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?string $navigationLabel = 'Certificates of Origin';
    protected static ?int $navigationSort = 23;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Header: 5. Document number, 5A. B/L number with shipment selection
                Forms\Components\Section::make('Header')
                    ->columns(12)
                    ->schema([
                        Forms\Components\Select::make('shipment_id')
                            ->label('Shipment (Booking #)')
                            ->options(function () {
                                $query = Shipment::query();
                                $user = Auth::user();
                                if ($user && !$user->isAdmin()) {
                                    $allowed = \App\Services\DocumentPermissionService::allowedCategoryIds($user);
                                    if (is_array($allowed)) {
                                        if (empty($allowed)) return [];
                                        $query->whereHas('indent.hsn', fn($q) => $q->whereIn('category_id', $allowed));
                                    }
                                }
                                return $query->orderByDesc('booking_date')->pluck('booking_no', 'id');
                            })
                            ->searchable()->preload()->native(false)
                            ->required()
                            ->default(fn() => request()->has('shipment_id') ? (int) request('shipment_id') : null)
                            ->reactive()
                            ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set, $state, $record) {
                                // On initial load, if shipment preselected and only one packing list, auto-select it and prefill
                                if (!$state) return;
                                // Respect explicit packing_list_id in request first
                                if (request()->has('packing_list_id')) {
                                    $set('packing_list_id', (int) request('packing_list_id'));
                                } else {
                                    $plIds = PackingList::query()->where('shipment_id', $state)->pluck('id');
                                    if ($plIds->count() === 1) {
                                        $set('packing_list_id', $plIds->first());
                                    }
                                }

                                // Prefill consigned_to if empty
                                $consigned = (string) $get('consigned_to');
                                if (trim($consigned) === '') {
                                    $shipment = Shipment::with('indent')->find($state);
                                    if ($shipment && $shipment->indent) {
                                        $name = trim((string) $shipment->indent->consignee_name);
                                        $addr = trim((string) $shipment->indent->consignee_address);
                                        $block = trim(($name ? (strtoupper($name)."\n") : '') . $addr);
                                        if ($block !== '') { $set('consigned_to', $block); }
                                    }
                                }
                                // Prefill document & B/L number from booking number if empty
                                $shipment = isset($shipment) ? $shipment : Shipment::find($state);
                                if ($shipment && empty($get('document_no'))) $set('document_no', $shipment->booking_no);
                                if ($shipment && empty($get('bl_no'))) $set('bl_no', $shipment->booking_no);
                            })
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                // When shipment changes, clear packing list select and try to prefill consigned_to if empty
                                $set('packing_list_id', null);
                                // Auto-select packing list if only one exists for the chosen shipment
                                if ($state) {
                                    $plIds = PackingList::query()->where('shipment_id', $state)->pluck('id');
                                    if ($plIds->count() === 1) {
                                        $set('packing_list_id', $plIds->first());
                                    }
                                }
                                $consigned = (string) $get('consigned_to');
                                if (trim($consigned) === '' && $state) {
                                    $shipment = Shipment::with('indent')->find($state);
                                    if ($shipment && $shipment->indent) {
                                        $name = trim((string) $shipment->indent->consignee_name);
                                        $addr = trim((string) $shipment->indent->consignee_address);
                                        $block = trim(($name ? (strtoupper($name)."\n") : '') . $addr);
                                        if ($block !== '') {
                                            $set('consigned_to', $block);
                                        }
                                    }
                                }
                                // Prefill document & B/L number from booking number if empty
                                if ($state) {
                                    $shipment = Shipment::find($state);
                                    if ($shipment) {
                                        if (empty($get('document_no'))) $set('document_no', $shipment->booking_no);
                                        if (empty($get('bl_no'))) $set('bl_no', $shipment->booking_no);
                                    }
                                }
                            })
                            ->columnSpan(4),
                        Forms\Components\Select::make('packing_list_id')
                            ->label('Packing List')
                            ->options(function (Forms\Get $get) {
                                $shipmentId = $get('shipment_id');
                                $query = PackingList::query();
                                if ($shipmentId) $query->where('shipment_id', $shipmentId);
                                return $query->orderByDesc('id')->pluck('id', 'id');
                            })
                            ->helperText('Optional: link to a specific packing list for precise totals and consigned details')
                            ->default(fn() => request()->has('packing_list_id') ? (int) request('packing_list_id') : null)
                            ->reactive()
                            ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set, $state, $record) {
                                if ($state) return; // already selected
                                $shipmentId = $get('shipment_id') ?: ($record?->shipment_id ?? null);
                                if ($shipmentId) {
                                    $plIds = PackingList::query()->where('shipment_id', $shipmentId)->pluck('id');
                                    if ($plIds->count() === 1) {
                                        $set('packing_list_id', $plIds->first());
                                        // Also auto-populate totals/description from this PL
                                        $pl = PackingList::with(['items', 'shipment.indent'])->find($plIds->first());
                                        if ($pl) {
                                            $totalBales = (int) ($pl->total_bales ?? $pl->items->sum('no_of_bales'));
                                            $totalKg = (float) ($pl->total_weight_kg ?? $pl->items->sum(function ($i) { return !is_null($i->weight_kg) ? (float)$i->weight_kg : round(((float)($i->weight_lbs ?? 0)) * 0.453592, 3); }));
                                            $containerCount = $pl->items->count();
                                            $desc = $pl->items->first()->description
                                                ?? optional($pl->shipment?->indent)->hsn_description
                                                ? (trim((string) optional($pl->shipment?->indent)->hsn_description) . (optional($pl->shipment?->indent)->hsn_code ? (' HS CODE ' . trim((string) optional($pl->shipment?->indent)->hsn_code)) : ''))
                                                : null;
                                            $set('total_bales', $totalBales ?: null);
                                            $set('total_gross_weight_kg', $totalKg ?: null);
                                            $set('total_packages', $containerCount ?: null);
                                            if (empty($get('description')) && $desc) $set('description', $desc);
                                            // Additional fields sourced from Packing List / Shipment
                                            if (empty($get('port_of_loading')) && !empty($pl->origin)) $set('port_of_loading', $pl->origin);
                                            if (empty($get('port_of_discharge')) && !empty($pl->destination)) $set('port_of_discharge', $pl->destination);
                                            if (empty($get('shipped_date')) && !empty($pl->ship_date)) $set('shipped_date', $pl->ship_date);
                                            if (empty($get('export_references')) && !empty($pl->shipment?->booking_no)) $set('export_references', $pl->shipment->booking_no);
                                            $set('containerized', !empty($pl->ship_in));
                                            if (empty($get('type_of_move'))) $set('type_of_move', 'Vessel, Containerized');
                                        }
                                    }
                                }
                            })
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                // Prefill consigned_to from packing list contact/indent if empty
                                $consigned = (string) $get('consigned_to');
                                if (trim($consigned) === '' && $state) {
                                    $pl = PackingList::with('shipment.indent')->find($state);
                                    if ($pl) {
                                        $name = $pl->shipment?->indent?->consignee_name ?: $pl->contact;
                                        $addr = $pl->shipment?->indent?->consignee_address;
                                        $block = trim((($name ? (strtoupper($name)."\n") : '')) . ($addr ?: ''));
                                        if ($block !== '') $set('consigned_to', $block);
                                    }
                                }
                                // Auto-fill totals & description
                                if ($state) {
                                    $pl = PackingList::with(['items', 'shipment.indent'])->find($state);
                                    if ($pl) {
                                        $totalBales = (int) ($pl->total_bales ?? $pl->items->sum('no_of_bales'));
                                        $totalKg = (float) ($pl->total_weight_kg ?? $pl->items->sum(function ($i) { return !is_null($i->weight_kg) ? (float)$i->weight_kg : round(((float)($i->weight_lbs ?? 0)) * 0.453592, 3); }));
                                        $containerCount = $pl->items->count();
                                        $desc = $pl->items->first()->description
                                            ?? (optional($pl->shipment?->indent)->hsn_description && optional($pl->shipment?->indent)->hsn_code
                                                ? (trim((string) optional($pl->shipment?->indent)->hsn_description) . ' HS CODE ' . trim((string) optional($pl->shipment?->indent)->hsn_code))
                                                : optional($pl->shipment?->indent)->hsn_description);
                                        $set('total_bales', $totalBales ?: null);
                                        $set('total_gross_weight_kg', $totalKg ?: null);
                                        $set('total_packages', $containerCount ?: null);
                                        if (empty($get('description')) && $desc) $set('description', $desc);
                                        // Additional fields sourced from Packing List / Shipment
                                        if (empty($get('port_of_loading')) && !empty($pl->origin)) $set('port_of_loading', $pl->origin);
                                        if (empty($get('port_of_discharge')) && !empty($pl->destination)) $set('port_of_discharge', $pl->destination);
                                        if (empty($get('shipped_date')) && !empty($pl->ship_date)) $set('shipped_date', $pl->ship_date);
                                        if (empty($get('export_references')) && !empty($pl->shipment?->booking_no)) $set('export_references', $pl->shipment->booking_no);
                                        $set('containerized', !empty($pl->ship_in));
                                        if (empty($get('type_of_move'))) $set('type_of_move', 'Vessel, Containerized');
                                    }
                                }
                            })
                            ->native(false)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('document_no')->label('5. Document number')->required()->unique(ignoreRecord: true)->columnSpan(3),
                        Forms\Components\TextInput::make('bl_no')->label('5A. B/L number')->nullable()->columnSpan(3),
                    ]),

                // 2. Exporter (read-only from Company Settings)
                Forms\Components\Section::make('2. Exporter (from Company Settings)')
                    ->description('Principal or seller-licensed and address including ZIP Code')
                    ->columns(12)
                    ->schema([
                        Forms\Components\Placeholder::make('exporter_company')->label('Company')
                            ->content(fn() => \App\Models\CompanySetting::query()->value('company_name'))
                            ->columnSpan(4),
                        Forms\Components\Placeholder::make('exporter_address')->label('Address')
                            ->content(function () {
                                $s = \App\Models\CompanySetting::query()->first();
                                return collect([$s?->company_address, $s?->company_address_line2])->filter()->implode("\n");
                            })
                            ->columnSpan(4),
                        Forms\Components\Placeholder::make('exporter_city_state')->label('City/State/Country')
                            ->content(function () {
                                $s = \App\Models\CompanySetting::query()->first();
                                return trim(($s?->company_city ?: '').', '.($s?->company_state ?: '').', '.($s?->company_country ?: ''));
                            })
                            ->columnSpan(3),
                        Forms\Components\Placeholder::make('exporter_zip')->label('ZIP Code')
                            ->content(fn() => \App\Models\CompanySetting::query()->value('company_zip'))
                            ->columnSpan(1),
                    ]),

                // 10-17 Routing / Transport fields mirroring PDF
                Forms\Components\Section::make('Routing & Transport')
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('place_of_receipt')->label('13. Place of Receipt by Pre-Carrier')->nullable()->columnSpan(4),
                        Forms\Components\TextInput::make('exporting_carrier')->label('14. Exporting Carrier')->nullable()->columnSpan(4),
                        Forms\Components\TextInput::make('port_of_loading')->label('15. Port of Loading/Export')->nullable()->columnSpan(4),
                        Forms\Components\TextInput::make('port_of_discharge')->label('16. Area/Port of Discharge')->nullable()->columnSpan(4),
                        Forms\Components\TextInput::make('place_of_delivery_on_carrier')->label('17. Place of Delivery by On-Carrier')->nullable()->columnSpan(4),
                        Forms\Components\TextInput::make('type_of_move')->label('11. Type of Move')->default('Vessel, Containerized')->nullable()->columnSpan(4),
                        Forms\Components\Toggle::make('containerized')->label('11a. Containerized (Vessel only)')->default(true)->columnSpan(4),
                    ]),

                // 6 & 7: Export References and Forwarding Agent
                Forms\Components\Section::make('References & Parties')
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('export_references')->label('6. Export References')->nullable()->columnSpan(6),
                        Forms\Components\TextInput::make('forwarding_agent')->label('7. Forwarding Agent')->nullable()->columnSpan(6),
                    ]),

                // 3, 4, 8, 9, 10 front block fields to mirror PDF
                Forms\Components\Section::make('Front Block (3, 4, 8, 9, 10)')
                    ->columns(12)
                    ->schema([
                        Forms\Components\Textarea::make('consigned_to')
                            ->label('3. Consigned To')
                            ->rows(4)
                            ->placeholder('If empty, auto-fills from Packing List / Shipment')
                            ->nullable()
                            ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set, $state, $record) {
                                if (trim((string) $state) !== '') return;
                                $plId = $get('packing_list_id') ?: ($record?->packing_list_id ?? null);
                                if ($plId) {
                                    $pl = PackingList::with('shipment.indent')->find($plId);
                                    if ($pl) {
                                        $name = $pl->shipment?->indent?->consignee_name ?: $pl->contact;
                                        $addr = $pl->shipment?->indent?->consignee_address;
                                        $block = trim((($name ? (strtoupper($name)."\n") : '')) . ($addr ?: ''));
                                        if ($block !== '') { $set('consigned_to', $block); return; }
                                    }
                                }
                                $shipmentId = $get('shipment_id') ?: ($record?->shipment_id ?? null);
                                if ($shipmentId) {
                                    $shipment = Shipment::with('indent')->find($shipmentId);
                                    if ($shipment && $shipment->indent) {
                                        $name = trim((string) $shipment->indent->consignee_name);
                                        $addr = trim((string) $shipment->indent->consignee_address);
                                        $block = trim(($name ? (strtoupper($name)."\n") : '') . $addr);
                                        if ($block !== '') { $set('consigned_to', $block); }
                                    }
                                }
                            })
                            ->columnSpan(6),
                        Forms\Components\Textarea::make('notify_party')->label('4. Notify Party / Intermediate Consignee')->rows(4)->placeholder('If empty, will use Shipment → Indent notify party')->nullable()->columnSpan(6),
                        Forms\Components\TextInput::make('origin_or_ftz')->label('8. Point (State) of Origin or FTZ Number')->placeholder('If empty, will use Company state/country')->nullable()->columnSpan(4),
                        Forms\Components\TextInput::make('domestic_routing_instructions')->label('9. Domestic Routing / Export Instructions')->nullable()->columnSpan(4),
                        Forms\Components\TextInput::make('pre_carriage_by')->label('10. Pre-Carriage By')->nullable()->columnSpan(4),
                    ]),

                // Totals / description block like table foot
                Forms\Components\Section::make('Totals & Description')
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('total_packages')->label('Containers / Packages')->numeric()->nullable()->columnSpan(4),
                        Forms\Components\TextInput::make('total_bales')->label('Total bales')->numeric()->nullable()->columnSpan(4),
                        Forms\Components\TextInput::make('total_gross_weight_kg')->label('Total Gross Wt (Kg)')->numeric()->nullable()->columnSpan(4),
                        Forms\Components\Textarea::make('description')->label('Description of Commodities')->rows(3)->nullable()->columnSpanFull(),
                    ]),

                // Dates, signatures, status block
                Forms\Components\Section::make('Dates, Signatures & Status')
                    ->columns(12)
                    ->schema([
                        Forms\Components\DatePicker::make('shipped_date')->label('Shipment Date')->native(false)->nullable()->columnSpan(4),
                        Forms\Components\DatePicker::make('sworn_date')->label('Sworn date')->native(false)->nullable()->columnSpan(4),
                        Forms\Components\FileUpload::make('owner_signature_path')
                            ->label('Signature of Owner or Agent')
                            ->image()
                            ->downloadable()
                            ->directory('signatures')
                            ->disk('public')
                            ->nullable()
                            ->columnSpan(4),
                        Forms\Components\FileUpload::make('chamber_signature_path')
                            ->label('Authorized Signature (Chamber)')
                            ->image()
                            ->downloadable()
                            ->directory('signatures')
                            ->disk('public')
                            ->nullable()
                            ->columnSpan(4),
                        Forms\Components\FileUpload::make('pdf_path')
                            ->label('Signed PDF (latest)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->downloadable()
                            ->directory('signed-coo')
                            ->disk('public')
                            ->nullable()
                            ->columnSpan(4),
                        Forms\Components\Select::make('status')->label('Status')->options([
                            'draft' => 'Draft',
                            'owner_signed' => 'Owner Signed',
                            'chamber_signed' => 'Chamber Signed',
                            'completed' => 'Completed',
                        ])->native(false)->required()->default('draft')->columnSpan(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_no')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('shipment.booking_no')->label('BKG #')->searchable(),
                Tables\Columns\TextColumn::make('packing_list')
                    ->label('Packing List')
                    ->getStateUsing(fn ($record) => optional($record->packingList)->id)
                    ->url(fn ($record) => $record->packingList ? PackingListResource::getUrl('edit', ['record' => $record->packingList->getKey()]) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('containers')
                    ->label('Containers')
                    ->getStateUsing(function ($record) {
                        $pl = $record->packingList;
                        if (!$pl) return null;
                        if (!empty($pl->container_no_summary)) return $pl->container_no_summary;
                        $count = (int) $pl->items()->count();
                        $shipIn = strtoupper((string) $pl->ship_in);
                        return $count > 0 && $shipIn ? ($count . ' x ' . $shipIn) : null;
                    }),
                Tables\Columns\TextColumn::make('first_container')
                    ->label('First container')
                    ->getStateUsing(function ($record) {
                        $pl = $record->packingList;
                        if (!$pl) return null;
                        $first = $pl->items()->orderBy('id')->first();
                        return $first?->container_no;
                    }),
                Tables\Columns\TextColumn::make('bl_no')->label('B/L')->toggleable(),
                Tables\Columns\TextColumn::make('port_of_loading')->toggleable(),
                Tables\Columns\TextColumn::make('port_of_discharge')->toggleable(),
                Tables\Columns\TextColumn::make('export_references')->label('Export Ref')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('forwarding_agent')->label('Forwarding Agent')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pdf_path')->label('Signed PDF Path')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('signed_pdf')
                    ->label('Signed PDF')
                    ->getStateUsing(fn ($record) => $record->pdf_path ? 'Download' : null)
                    ->url(fn ($record) => $record->pdf_path ? asset('storage/' . ltrim($record->pdf_path, '/')) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'draft' => 'Draft',
                        'owner_signed' => 'Owner Signed',
                        'chamber_signed' => 'Chamber Signed',
                        'completed' => 'Completed',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'owner_signed',
                        'info' => 'chamber_signed',
                        'success' => 'completed',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'owner_signed' => 'Owner Signed',
                        'chamber_signed' => 'Chamber Signed',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadSignedPdf')
                    ->label('Signed PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (CertificateOfOrigin $record) => !empty($record->pdf_path))
                    ->url(fn (CertificateOfOrigin $record) => $record->pdf_path ? asset('storage/' . ltrim($record->pdf_path, '/')) : null)
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (CertificateOfOrigin $record) => route('certificates.pdf', ['certificate' => $record->getKey(), 'download' => 0]))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificatesOfOrigin::route('/'),
            'create' => Pages\CreateCertificateOfOrigin::route('/create'),
            'edit' => Pages\EditCertificateOfOrigin::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        if ($user && !$user->isAdmin()) {
            $allowed = \App\Services\DocumentPermissionService::allowedCategoryIds($user);
            if (is_array($allowed)) {
                if (empty($allowed)) return $query->whereRaw('1 = 0');
                $query = $query->whereHas('shipment.indent.hsn', function ($q) use ($allowed) {
                    $q->whereIn('category_id', $allowed);
                });
            }
        }
        return $query;
    }
}
