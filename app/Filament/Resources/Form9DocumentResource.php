<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Form9DocumentResource\Pages;
use App\Models\Form9Document;
use App\Models\Shipment;
use App\Models\BlCorrection;
use App\Models\Invoice;
use App\Services\DocumentPermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class Form9DocumentResource extends Resource
{
    protected static ?string $model = Form9Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?string $navigationLabel = 'Form 9';
    protected static ?int $navigationSort = 24;

    protected static string $permissionResource = 'form9';

    public static function form(Form $form): Form
    {
        return $form->extraAttributes(['novalidate' => true])->schema([
            Wizard::make()
                ->columnSpanFull()
                ->skippable()
                ->steps([
                
                Step::make('1. Exporter & 1(ii) Generator & Quick Status')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                    Forms\Components\Section::make('Exporter Information')
                        ->description('Details of the exporter')
                        ->columns(2)
                        ->schema([
                            Forms\Components\Textarea::make('exporter_name_address')
                                ->label('Name & Address')
                                ->rows(4)
                                ->required()
                                ->columnSpan(1),
                            Forms\Components\Group::make()
                                ->columnSpan(1)
                                ->schema([
                                    Forms\Components\TextInput::make('exporter_contact_person')
                                        ->label('Contact Person'),
                                    Forms\Components\TextInput::make('exporter_phone')
                                        ->label('Phone')
                                        ->tel(),
                                    Forms\Components\TextInput::make('exporter_email')
                                        ->label('Email')
                                        ->email(),
                                ]),
                        ]),
                    Forms\Components\Section::make('Generator Information')
                        ->description('Details of the waste generator')
                        ->columns(2)
                        ->schema([
                            Forms\Components\Textarea::make('generator_name_address')
                                ->label('Name & Address')
                                ->required()
                                ->rows(4)
                                ->columnSpan(1),
                            Forms\Components\Group::make()
                                ->columnSpan(1)
                                ->schema([
                                    Forms\Components\TextInput::make('generator_contact_person')
                                    ->required()    
                                    ->label('Contact Person'),
                                    Forms\Components\TextInput::make('generator_phone')
                                        ->label('Phone')
                                        ->tel(),
                                    Forms\Components\TextInput::make('generator_email')
                                        ->label('Email')
                                        ->email(),
                                ]),
                            Forms\Components\TextInput::make('site_of_generation')
                                ->label('Site of Generation')
                                ->columnSpanFull(),
                        ]),
                    Forms\Components\Section::make('Quick Status')
                        ->description('Update current status quickly')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->required()
                                ->options([
                                    'draft' => 'Draft',
                                    'submitted' => 'Submitted',
                                    'approved' => 'Approved',
                                    'shipped' => 'Shipped',
                                    // 'received_importer' => 'Received by Importer',
                                    'received_recycler' => 'Received by Recycler',
                                    // 'recycling_scheduled' => 'Recycling Scheduled',
                                    // 'recycling_completed' => 'Recycling Completed',
                                    'completed' => 'Completed',
                                ])
                                ->native(false)
                                ->reactive(),
                            Forms\Components\FileUpload::make('pdf_path')
                                ->label('Upload latest PDF')
                                ->helperText('Upload the latest PDF when sent to/received by recycler')
                                ->acceptedFileTypes(['application/pdf'])
                                ->directory('form9/uploads')
                                ->disk('public')
                                ->downloadable()
                                ->openable()
                                ->visible(fn(\Filament\Forms\Get $get) => in_array($get('status'), ['received_recycler','recycling_scheduled','recycling_completed','completed']))
                                ->columnSpan(1),
                        ]),
                ]),

                
                Step::make('2 & 6. Importer/Recycler & Disposer')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                    Forms\Components\Section::make('2. Importer / Recycler')
                        ->description('Name & Address of importer/recycler')
                        ->columns(2)
                        ->schema([
                            Forms\Components\Textarea::make('importer_name_address')
                                ->label('Name & Address')
                                ->rows(4)
                                ->required()
                                ->columnSpan(1),
                            Forms\Components\Group::make()
                                ->columnSpan(1)
                                ->schema([
                                    Forms\Components\TextInput::make('importer_contact_person')
                                        ->required()    
                                        ->label('Contact Person'),
                                    Forms\Components\TextInput::make('importer_phone')
                                        ->label('Phone')
                                        ->tel(),
                                    Forms\Components\TextInput::make('importer_email')
                                        ->label('Email')
                                        ->email(),
                                ]),
                        ]),
                    Forms\Components\Section::make('6. Disposer')
                        ->description('Disposer name, address and site details')
                        ->columns(2)
                        ->schema([
                            Forms\Components\Textarea::make('disposer_name_address')
                                ->label('Name & Address')
                                ->rows(4)
                                ->required()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('disposer_contact_person')
                                ->label('Contact Person')
                                ->columnSpan(1),
                            Forms\Components\Textarea::make('actual_site_of_disposal')
                                ->label('Actual Site of Disposal')
                                ->rows(rows: 3)
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('disposer_phone')
                                ->label('Telephone / Fax')
                                ->tel()
                                ->columnSpan(1),
                        ]),
                ]),
                
                Step::make('3-4. Reference & BL')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                    Forms\Components\Section::make('Shipment References')
                        ->description('Select shipment, invoice and BL details')
                        ->columns(3)
                        ->schema([
                            Forms\Components\Select::make('shipment_id')
                                ->label('Shipment (BKG #)')
                                ->options(function(){
                                    $query = Shipment::query();
                                    $user = Auth::user();
                                    if ($user && !$user->isAdmin()) {
                                        $allowed = \App\Services\DocumentPermissionService::allowedCategoryIds($user);
                                        if (is_array($allowed)) {
                                            if (empty($allowed)) return [];
                                            $query->whereHas('indent.hsn', fn($q)=> $q->whereIn('category_id', $allowed));
                                        }
                                    }
                                    return $query->orderByDesc('booking_date')->pluck('booking_no','id');
                                })
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, \Filament\Forms\Set $set) {
                                    // Reset dependent selects when shipment changes
                                    $set('invoice_id', null);
                                    $set('bl_correction_id', null);
                                    $set('bill_of_lading', null);
                                })
                                ->columnSpan(1),
                            Forms\Components\Select::make('invoice_id')
                                ->label('Invoice')
                                ->options(function(\Filament\Forms\Get $get){
                                    $query = Invoice::query()->latest('id');
                                    $shipmentId = $get('shipment_id');
                                    if ($shipmentId) {
                                        $query->where('shipment_id', $shipmentId);
                                    }
                                    $user = Auth::user();
                                    if ($user && !$user->isAdmin()) {
                                        $allowed = \App\Services\DocumentPermissionService::allowedCategoryIds($user);
                                        if (is_array($allowed)) {
                                            if (empty($allowed)) return [];
                                            $query->whereHas('shipment.indent.hsn', fn($q)=> $q->whereIn('category_id', $allowed));
                                        }
                                    }
                                    return $query->with('shipment')->get()->mapWithKeys(function ($inv) {
                                        $label = ($inv->invoice_no ?: ('ID#'.$inv->id))
                                            .' · BKG '.($inv->shipment?->booking_no ?? '-')
        				.' · $'.(string)($inv->amount ?? '-');
                                        return [$inv->id => $label];
                                    })->toArray();
                                })
                                ->searchable()
                                ->required()
                                ->preload()
                                ->native(false)
                                ->rule(function (\Filament\Forms\Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $shipmentId = $get('shipment_id');
                                        if ($shipmentId && $value) {
                                            $ok = Invoice::query()->where('id', $value)->where('shipment_id', $shipmentId)->exists();
                                            if (!$ok) {
                                                $fail('Selected Invoice does not belong to the chosen Shipment.');
                                            }
                                        }
                                    };
                                })
                                ->columnSpan(1),
                            Forms\Components\Select::make('bl_correction_id')
                                ->label('BL')
                                ->required()
                                ->options(function(\Filament\Forms\Get $get){
                                    $query = BlCorrection::query()->latest('id');
                                    $shipmentId = $get('shipment_id');
                                    if ($shipmentId) {
                                        $query->where('shipment_id', $shipmentId);
                                    }
                                    $user = Auth::user();
                                    if ($user && !$user->isAdmin()) {
                                        $allowed = \App\Services\DocumentPermissionService::allowedCategoryIds($user);
                                        if (is_array($allowed)) {
                                            if (empty($allowed)) return [];
                                            $query->whereHas('shipment.indent.hsn', fn($q)=> $q->whereIn('category_id', $allowed));
                                        }
                                    }
                                    return $query->get()->mapWithKeys(function ($bl) {
                                        $label = (($bl->bl_no ?: ('BL#'.$bl->id))).' · BKG '.($bl->booking_no ?? '-')
                                            .' · '.$bl->destination;
                                        return [$bl->id => $label];
                                    })->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->reactive()
                                ->afterStateUpdated(function ($state, \Filament\Forms\Set $set) {
                                    $bl = $state ? \App\Models\BlCorrection::find($state) : null;
                                    $set('bill_of_lading', $bl?->booking_no);
                                })
                                ->rule(function (\Filament\Forms\Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $shipmentId = $get('shipment_id');
                                        if ($shipmentId && $value) {
                                            $ok = BlCorrection::query()->where('id', $value)->where('shipment_id', $shipmentId)->exists();
                                            if (!$ok) {
                                                $fail('Selected BL does not belong to the chosen Shipment.');
                                            }
                                        }
                                    };
                                })
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('bill_of_lading')
                                ->label('4. Bill of Lading')
                                ->afterStateHydrated(function (\Filament\Forms\Set $set, $state, $record) {
                                    if (($state === null || trim((string)$state) === '') && $record && $record->bl) {
                                        $set('bill_of_lading', $record->bl->booking_no);
                                    }
                                })
                                ->columnSpan(1),
                        ]),
                    Forms\Components\Section::make('Form 9 Details')
                        ->description('Enter Form 9 reference information')
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('form9_no')
                                ->label('Form9 Number')
                                ->required()
                                ->columnSpan(1),
                            Forms\Components\DatePicker::make('form9_date')
                                ->label('Form9 Date')
                                ->native(false)
                                ->required()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('applicant_ref_no')
                                ->label('Applicant Reference No')
                                // ->required()
                                ->columnSpan(1),
                        ]),
                    Forms\Components\Select::make('movement_type')
                        ->label('Movement Type')
                        ->options(['single'=>'Single','multiple'=>'Multiple'])
                        ->default('single')
                        ->native(false)
                        ->columnSpan(1),
                ]),
                
                Step::make('5. Carriers')
                    ->icon('heroicon-o-truck')
                    ->schema([
                    Forms\Components\Section::make('Carrier Information')
                        ->description('Add first, second, and last carrier details')
                        ->schema([
                            Forms\Components\Repeater::make('carriers')
                                ->relationship('carriers')
                                ->label('')
                                ->minItems(1)
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Select::make('carrier_type')
                                        ->label('Carrier Type')
                                        ->options([
                                            'first'=>'5(a) 1st Carrier',
                                            'second'=>'5(b) 2nd Carrier',
                                            'last'=>'5(c) Last Carrier'
                                        ])
                                        ->native(false)
                                        ->required()
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('registration_no')
                                        ->label('Registration Number')
                                        ->columnSpan(1),
                                    Forms\Components\Textarea::make('name_address')
                                        ->label('Name & Address')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Telephone / Fax')
                                        ->tel()
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('transport_identity')
                                        ->label('Identity of Means of Transport')
                                        ->columnSpan(1),
                                    Forms\Components\DatePicker::make('transfer_date')
                                        ->label('Date of Transfer')
                                        ->native(false)
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('signature')
                                        ->label("Carrier's Representative Signature")
                                        ->columnSpan(1),
                                    Forms\Components\FileUpload::make('signature_image_path')
                                        ->label('Signature Image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('form9/signatures')
                                        ->downloadable()
                                        ->columnSpanFull(),
                                ])
                                ->itemLabel(function (array $state): string {
                                    $map = [
                                        'first' => '5(a) 1st Carrier',
                                        'second' => '5(b) 2nd Carrier',
                                        'last' => '5(c) Last Carrier',
                                    ];
                                    $key = $state['carrier_type'] ?? null;
                                    return $map[$key] ?? 'Carrier';
                                })
                                ->orderable(false)
                                ->addActionLabel('Add Carrier')
                                ->columnSpanFull(),
                        ]),
                ]),
                

                Step::make('7-10. Waste & Packaging')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                    Forms\Components\Section::make('Recovery Method')
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('method_of_recovery')
                                ->label('7. Method(s) of Recovery')
                                ->required()
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('r_code')
                                ->label('R Code')
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('technology_employed')
                                ->label('Technology Employed')
                                ->required()
                                ->columnSpanFull(),
                        ]),
                    Forms\Components\Section::make('Waste Details')
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('waste_designation_composition')
                                ->label('8. Designation and Chemical Composition')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('physical_characteristics')
                                ->label('9. Physical Characteristics')
                                ->default('Solid')
                                ->required()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('actual_quantity_kgs')
                                ->label('10. Actual Quantity (Kg/Lt)')
                                ->required()
                                ->numeric()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('waste_description')
                                ->label('10. Waste Description')
                                ->columnSpan(1),
                        ]),
                ]),
                
                Step::make('11. Waste Identification Code')
                    ->icon('heroicon-o-hashtag')
                    ->schema([
                    Forms\Components\Section::make('Waste Identification Codes')
                        ->description('Enter all applicable waste identification codes')
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('waste_identification_code')
                                ->label('11. Waste Identification Code')
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('basel_no')
                                ->label('Basel No.')
                                ->default('B3020')
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('oecd_no')
                                ->label('OECD No.')
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('un_no')
                                ->label('UN No.')
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('itc_hs')
                                ->label('ITC (HS)')
                                ->default('4707.90')
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('customs_code_hs')
                                ->label('Customs Code (HS)')
                                ->default('47079000')
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('other_codes')
                                ->label('Other Codes (Specify)')
                                ->columnSpanFull(),
                        ]),
                ]),
                
                Step::make('12. OECD Classification')
                    ->icon('heroicon-o-tag')
                    ->schema([
                    Forms\Components\Section::make('OECD Classification Details')
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('oecd_classification')
                                ->label('OECD Classification')
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('oecd_color')
                                ->label('Color (Amber/Red/Other)')
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('oecd_number')
                                ->label('(b) Number')
                                ->columnSpan(1),
                        ]),
                ]),

                Step::make('13. Packaging')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                    Forms\Components\Section::make('Packaging Information')
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('packaging_type')
                                ->label('13. Packaging Type')
                                ->default('Bales')
                                ->columnSpan(1),
                            // Forms\Components\Select::make('packaging_type')
                            //     ->label('13. Packaging Type')
                            //     ->options([
                            //         'Drum' => 'Drum',
                            //         'Wooden barrel' => 'Wooden barrel',
                            //         'Jerrican' => 'Jerrican',
                            //         'Box' => 'Box',
                            //         'Bag' => 'Bag',
                            //         'Composite packaging' => 'Composite packaging',
                            //         'Pressure receptacle' => 'Pressure receptacle',
                            //         'Bulk' => 'Bulk',
                            //         'Other (Specify)' => 'Other (Specify)',
                            //     ])
                            //     ->native(false)
                            //     ->preload()
                            //     ->columnSpan(1),
                            Forms\Components\TextInput::make('packaging_number')
                                ->label('13. Number of Packages')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->columnSpan(1),
                        ]),
                ]),

                Step::make('14. UN Classification')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                    Forms\Components\Section::make('UN Classification Details')
                        ->description('United Nations classification for hazardous materials')
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('un_classification')
                                ->label('UN Classification')
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('un_shipping_name')
                                ->label('UN Shipping Name')
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('un_identification_no')
                                ->label('UN Identification No.')
                                ->columnSpan(1),
                            Forms\Components\Select::make('un_class')
                                ->label('UN Class')
                                ->options([
                                    '1'=>'1', '3'=>'3', '4.1'=>'4.1', '4.2'=>'4.2', '4.3'=>'4.3', 
                                    '5.1'=>'5.1', '5.2'=>'5.2', '6.1'=>'6.1', '6.2'=>'6.2', '8'=>'8', '9'=>'9'
                                ])
                                ->native(false)
                                ->preload()
                                ->columnSpan(1),
                            Forms\Components\Select::make('h_number')
                                ->label('H Number')
                                ->options([
                                    'H1'=>'H 1', 'H3'=>'H 3', 'H4.1'=>'H 4.1', 'H4.2'=>'H 4.2', 'H4.3'=>'H 4.3', 
                                    'H5.1'=>'H 5.1', 'H5.2'=>'H 5.2', 'H6.1'=>'H 6.1', 'H6.2'=>'H 6.2', 
                                    'H8'=>'H 8', 'H10'=>'H 10', 'H11'=>'H 11', 'H12'=>'H 12', 'H13'=>'H 13'
                                ])
                                ->native(false)
                                ->preload()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('y_number')
                                ->label('Y Number')
                                ->columnSpan(1),
                        ]),
                ]),
                
                Step::make('15-16. Shipment & Handling')
                    ->icon('heroicon-o-paper-airplane')
                    ->schema([
                    Forms\Components\Section::make('Shipment Details')
                        ->columns(columns: 3)
                        ->schema([
                            // Forms\Components\Select::make('means_of_transport')
                            //     ->label('15. Means of Transport')
                            //     ->options([
                            //         'R' => 'R = Road',
                            //         'T' => 'T = Train/Rail',
                            //         'S' => 'S = Sea',
                            //         'A' => 'A = Air',
                            //         'W' => 'W = Inland Water ways',
                            //     ])
                            //     ->default('S')
                            //     ->native(false)
                            //     ->searchable()
                            //     ->preload()
                            //     ->columnSpan(1),
                            Forms\Components\Textarea::make('special_handling_requirements')
                                ->label('15. Special Handling Requirements')
                                ->rows(2)
                                // ->columnSpan(2),
                                ->columnSpanFull(),
                            Forms\Components\DatePicker::make('actual_shipment_date')
                                ->label('16. Actual Date of Shipment')
                                ->required()
                                ->native(false)
                                ->columnSpan(1),
                        ]),
                ]),

              
                
                Step::make("17. Exporter's Declaration")
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                    Forms\Components\Section::make("Exporter's Declaration")
                        ->description('Declaration and signature of the exporter')
                        ->schema([
                            Forms\Components\Textarea::make('exporter_declaration')
                                ->label("17. Exporter's Declaration")
                                ->rows(3)
                                ->columnSpanFull(),
                            Forms\Components\Group::make()
                                ->columns(3)
                                ->schema([
                                    Forms\Components\TextInput::make('exporter_signature_name')
                                        ->label('Signature / Name')
                                        ->required()
                                        ->columnSpan(1),
                                    Forms\Components\DatePicker::make('exporter_declaration_date')
                                        ->label('Declaration Date')
                                        ->native(false)
                                        ->required()
                                        ->columnSpan(1),
                                    Forms\Components\FileUpload::make('exporter_signature_image_path')
                                        ->label('Signature Image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('form9/signatures')
                                        ->downloadable()
                                        ->columnSpan(1),
                                ]),
                        ]),
                ]),

                // Step::make('History')
                //     ->icon('heroicon-o-clock')
                //     ->schema([
                //     Forms\Components\Section::make('Recent Activity')
                //         ->description('Read-only history of this Form 9')
                //         ->schema([
                //             Forms\Components\Placeholder::make('history_list')
                //                 ->label('')
                //                 ->content(function ($record) {
                //                     if (!$record) return 'No history yet';
                //                     $rows = $record->histories()->latest('id')->take(20)->get()->map(function ($h) {
                //                         $when = $h->created_at?->diffForHumans();
                //                         $by = $h->user?->name ?: 'system';
                //                         $notes = $h->notes ?: '';
                //                         return "- {$when} • {$h->action} • {$by} " . ($notes ? "• {$notes}" : '');
                //                     })->toArray();
                //                     return empty($rows) ? 'No history yet' : implode("\n", $rows);
                //                 })
                //                 ->columnSpanFull(),
                //             Forms\Components\Section::make('Status & Latest PDF')
                //                 ->description('Quick update')
                //                 ->columns(2)
                //                 ->schema([
                //                     Forms\Components\Select::make('status')
                //                         ->label('Status')
                //                         ->options([
                //                             'draft' => 'Draft',
                //                             'submitted' => 'Submitted',
                //                             'approved' => 'Approved',
                //                             'shipped' => 'Shipped',
                //                             'received_importer' => 'Received by Importer',
                //                             'received_recycler' => 'Received by Recycler',
                //                             'recycling_scheduled' => 'Recycling Scheduled',
                //                             'recycling_completed' => 'Recycling Completed',
                //                             'completed' => 'Completed',
                //                         ])
                //                         ->native(false)
                //                         ->reactive()
                //                         ->columnSpan(1),
                //                     Forms\Components\FileUpload::make('pdf_path')
                //                         ->label('Upload latest PDF')
                //                         ->helperText('Upload the latest PDF when sent to/received by recycler')
                //                         ->acceptedFileTypes(['application/pdf'])
                //                         ->directory('form9/uploads')
                //                         ->disk('public')
                //                         ->downloadable()
                //                         ->openable()
                //                         ->visible(fn(\Filament\Forms\Get $get) => in_array($get('status'), ['received_recycler','recycling_scheduled','recycling_completed','completed']))
                //                         ->columnSpan(1),
                //                 ]),
                //         ]),
                // ]),
                
                // Step::make('Importer / Recycler')
                //     ->icon('heroicon-o-inbox-arrow-down')
                //     ->schema([
                //     Forms\Components\Section::make('Importer Receipt')
                //         ->description('Details of materials received by importer')
                //         ->columns(3)
                //         ->schema([
                //             Forms\Components\TextInput::make('quantity_received_importer')
                //                 ->label('Quantity Received')
                //                 ->numeric()
                //                 ->columnSpan(1),
                //             Forms\Components\DatePicker::make('date_received_importer')
                //                 ->label('Date Received')
                //                 ->native(false)
                //                 ->columnSpan(1),
                //             Forms\Components\TextInput::make('signature_received_importer')
                //                 ->label('Signature')
                //                 ->columnSpan(1),
                //         ]),
                //     Forms\Components\Section::make('Recycler Receipt')
                //         ->description('Details of materials received and accepted by recycler')
                //         ->columns(3)
                //         ->schema([
                //             Forms\Components\TextInput::make('quantity_received_recycler')
                //                 ->label('Quantity Received')
                //                 ->numeric()
                //                 ->columnSpan(1),
                //             Forms\Components\TextInput::make('quantity_accepted_recycler')
                //                 ->label('Quantity Accepted')
                //                 ->numeric()
                //                 ->columnSpan(1),
                //             Forms\Components\DatePicker::make('date_received_recycler')
                //                 ->label('Date Received')
                //                 ->native(false)
                //                 ->columnSpan(1),
                //             Forms\Components\Textarea::make('method_of_recycling')
                //                 ->label('Method of Recycling')
                //                 ->rows(2)
                //                 ->columnSpanFull(),
                //         ]),
                // ]),
                
                // Step::make('Recovery & Notes')->schema([
                //     // Recovery Operations removed from management UI; rendered as static image in PDF
                //     Forms\Components\Textarea::make('specific_conditions')->rows(2)->columnSpanFull(),
                //     Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
                // ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            // Tables\Columns\TextColumn::make('id')->label('F9 #')->sortable(),
            Tables\Columns\TextColumn::make('form9_no')->label('No')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('form9_date')->label('date')->date()->sortable(),
            Tables\Columns\TextColumn::make('invoice.invoice_no')->label('Invoice')->searchable(),
            Tables\Columns\TextColumn::make('shipment.booking_no')->label('BKG #')->searchable(),
            Tables\Columns\BadgeColumn::make('status')->colors([
                'gray' => 'draft',
                'info' => 'submitted',
                'success' => 'approved',
                'warning' => 'shipped',
                'primary' => 'received_importer',
                'secondary' => 'completed',
            ])->sortable(),
            Tables\Columns\TextColumn::make('pdf_path')
                ->label('Latest PDF')
                ->formatStateUsing(fn ($state) => $state ? 'Download' : '-')
                ->url(fn ($record) => $record?->pdf_path ? Storage::url($record->pdf_path) : null)
                ->openUrlInNewTab()
                ->toggleable()
                ->visible(fn ($record) => filled($record?->pdf_path)),
            Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Draft',
                    'submitted' => 'Submitted',
                    'approved' => 'Approved',
                    'shipped' => 'Shipped',
                    // 'received_importer' => 'Received by Importer',
                    'received_recycler' => 'Received by Recycler',
                    // 'recycling_scheduled' => 'Recycling Scheduled',
                    // 'recycling_completed' => 'Recycling Completed',
                    'completed' => 'Completed',
                ]),
        ])
        ->defaultSort('created_at', 'desc')
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('latestPdf')
                ->label('Latest PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn ($record) => $record?->pdf_path ? Storage::url($record->pdf_path) : null)
                ->openUrlInNewTab()
                ->visible(fn ($record) => filled($record?->pdf_path)),
            Tables\Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn(Form9Document $record) => route('form9.pdf', ['form9' => $record->getKey(), 'download' => 0]))
                ->openUrlInNewTab(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForm9Documents::route('/'),
            'create' => Pages\CreateForm9Document::route('/create'),
            'edit' => Pages\EditForm9Document::route('/{record}/edit'),
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
                $query = $query->where(function($q) use ($allowed){
                    $q->whereHas('shipment.indent.hsn', fn($qq)=> $qq->whereIn('category_id', $allowed))
                      ->orWhereHas('invoice.shipment.indent.hsn', fn($qq)=> $qq->whereIn('category_id', $allowed))
                      ->orWhereHas('bl.shipment.indent.hsn', fn($qq)=> $qq->whereIn('category_id', $allowed));
                });
            }
        }
        return $query;
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

    public static function getRelations(): array
    {
        // $user = Auth::user();
        // if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
        //     return [
        //         \App\Filament\Resources\Form9DocumentResource\RelationManagers\HistoriesRelationManager::class,
        //     ];
        // }
        return [];
    }
}