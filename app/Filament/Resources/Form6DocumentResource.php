<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Form6DocumentResource\Pages;
use App\Models\Form6Document;
use App\Models\Shipment;
use App\Models\BlCorrection;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class Form6DocumentResource extends Resource
{
    protected static ?string $model = Form6Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?string $navigationLabel = 'Form 6';
    protected static ?int $navigationSort = 23;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columns(12)->schema([
                Forms\Components\Select::make('shipment_id')
                    ->label('Shipment (BKG #)')
                    ->options(fn()=> Shipment::query()->orderByDesc('booking_date')->pluck('booking_no','id'))
                    ->searchable()->preload()->native(false)->required()->columnSpan(4),
                Forms\Components\Select::make('invoice_id')
                    ->label('Invoice')
                    ->options(fn()=> Invoice::query()->latest('id')->pluck('invoice_no','id'))
                    ->searchable()->preload()->native(false)->columnSpan(4),
                Forms\Components\Select::make('bl_correction_id')
                    ->label('BL')
                    ->options(fn()=> BlCorrection::query()->latest('id')->pluck('id','id'))
                    ->searchable()->preload()->native(false)->columnSpan(4),
                Forms\Components\TextInput::make('form6_no')->required()->columnSpan(4),
                Forms\Components\DatePicker::make('form6_date')->native(false)->required()->columnSpan(4),
                Forms\Components\TextInput::make('applicant_ref_no')->columnSpan(4),
            ]),

            Forms\Components\Section::make('1. Exporter')->schema([
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Textarea::make('exporter_name_address')
                        ->label('Exporter name address')
                        ->rows(4)
                        ->required()
                        ->columnSpan(6),
                    Forms\Components\Group::make()->columns(12)->columnSpan(6)->schema([
                        Forms\Components\TextInput::make('exporter_contact_person')->label('Exporter contact person')->required()->columnSpan(6),
                        Forms\Components\TextInput::make('exporter_phone')->label('Exporter phone')->tel()->columnSpan(6),
                        Forms\Components\TextInput::make('exporter_email')->label('Exporter email')->email()->columnSpan(12),
                    ]),
                ]),
            ]),

            Forms\Components\Section::make('2. Generator')->schema([
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Textarea::make('generator_name_address')
                        ->label('Generator name address')
                        ->rows(4)
                        ->required()
                        ->columnSpan(6),
                    Forms\Components\Group::make()->columns(12)->columnSpan(6)->schema([
                        Forms\Components\TextInput::make('generator_contact_person')->label('Generator contact person')->required()->columnSpan(6),
                        Forms\Components\TextInput::make('generator_phone')->label('Generator phone')->tel()->columnSpan(6),
                        Forms\Components\TextInput::make('generator_email')->label('Generator email')->email()->columnSpan(12),
                    ]),
                    Forms\Components\TextInput::make('site_of_generation')->label('Site of generation')->columnSpan(12),
                ]),
            ]),

            Forms\Components\Section::make('3. Importer / Actual user')->schema([
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Textarea::make('importer_name_address')
                        ->label('Importer name address')
                        ->rows(4)
                        ->required()
                        ->columnSpan(6),
                    Forms\Components\Group::make()->columns(12)->columnSpan(6)->schema([
                        Forms\Components\TextInput::make('importer_contact_person')->label('Importer contact person')->required()->columnSpan(6),
                        Forms\Components\TextInput::make('importer_phone')->label('Importer phone')->tel()->columnSpan(6),
                        Forms\Components\TextInput::make('importer_email')->label('Importer email')->email()->columnSpan(12),
                    ]),
                ]),
            ]),

            Forms\Components\Section::make('7-11. Shipment')->schema([
                Forms\Components\TextInput::make('bill_of_lading')->label('Bill of lading')->columnSpan(4),
                Forms\Components\TextInput::make('country_of_export')->default('United States')->columnSpan(4),
                Forms\Components\TextInput::make('country_of_import')->default('India')->columnSpan(4),
                Forms\Components\TextInput::make('quantity_kgs')->numeric()->required()->columnSpan(4),
                Forms\Components\TextInput::make('physical_characteristics')->default('Solid')->columnSpan(4),
                Forms\Components\TextInput::make('chemical_composition')->default('EN 643')->columnSpan(4),
                Forms\Components\TextInput::make('basel_no')->default('B3020')->columnSpan(4),
                Forms\Components\TextInput::make('itc_hs')->default('4707.90')->columnSpan(4),
                Forms\Components\TextInput::make('customs_code_hs')->default('47079000')->columnSpan(4),
                Forms\Components\TextInput::make('package_type')->default('BALES')->columnSpan(4),
                Forms\Components\TextInput::make('package_number')->numeric()->columnSpan(4),
                Forms\Components\Select::make('movement_type')
                    ->label('Movement subject to single / multiple consignment')
                    ->options(['single' => 'Single', 'multiple' => 'Multiple'])
                    ->default('single')
                    ->reactive()
                    ->columnSpan(6),
                Forms\Components\Textarea::make('expected_shipment_dates')
                    ->label('Expected dates of each shipment or expected frequency of the shipments')
                    ->rows(2)
                    ->visible(fn(Get $get) => $get('movement_type') === 'multiple')
                    ->columnSpan(6),
                Forms\Components\Textarea::make('estimated_quantities')
                    ->label('Estimated total quantity and quantities for each individual shipment')
                    ->rows(2)
                    ->visible(fn(Get $get) => $get('movement_type') === 'multiple')
                    ->columnSpan(6),
            ])->columns(12),

            // 12. Transporter of waste
            Forms\Components\Section::make('12. Transporter of waste')->schema([
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\Textarea::make('transporter_name_address')->label('Transporter name and address')->rows(3)->columnSpan(6),
                    Forms\Components\Group::make()->columns(12)->columnSpan(6)->schema([
                        Forms\Components\TextInput::make('transporter_registration_no')->label('Registration number')->columnSpan(12),
                        Forms\Components\TextInput::make('means_of_transport')->label('Means of transport')->default('sea')->columnSpan(6),
                        Forms\Components\DatePicker::make('date_of_transfer')->label('Date of transfer')->native(false)->columnSpan(6),
                        Forms\Components\TextInput::make('carrier_signature')->label("Signature of carrier's representative")->columnSpan(12),
                    ]),
                ]),
            ]),

            // 13. Exporter's declaration
            Forms\Components\Section::make("13. Exporter's declaration")->schema([
                Forms\Components\Textarea::make('exporter_declaration')->rows(3)->columnSpanFull(),
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\TextInput::make('exporter_signature_name')->label('Signature / Name')->columnSpan(6),
                    Forms\Components\DatePicker::make('exporter_signature_date')->label('Date')->native(false)->columnSpan(6),
                    Forms\Components\FileUpload::make('exporter_signature_image_path')
                        ->label('Signature image (exporter)')
                        ->image()->imageEditor(false)
                        ->disk('public')
                        ->directory('form6/signatures')
                        ->downloadable()
                        ->columnSpanFull(),
                ]),
            ]),

            // 14. Shipment received by importer
            Forms\Components\Section::make('14. Shipment received by importer')->schema([
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\TextInput::make('quantity_received_kgs')->label('Quantity received (kgs)')->numeric()->columnSpan(6),
                    Forms\Components\TextInput::make('importer_signature_name')->label('Importer signature / name')->columnSpan(3),
                    Forms\Components\DatePicker::make('importer_signature_date')->label('Importer date')->native(false)->columnSpan(3),
                ]),
            ]),

            // 15. Corresponding to applicant ref / R code / Technology employed
            Forms\Components\Section::make('15. Applicant Ref / R code / Technology')->schema([
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\TextInput::make('importer_applicant_ref_no')->label('Corresponding to applicant Ref No., if any')->columnSpan(4),
                    Forms\Components\TextInput::make('r_code')->label('R code')->columnSpan(4),
                    Forms\Components\TextInput::make('technology_employed')->label('Technology employed')->columnSpan(4),
                ]),
            ]),

            // 16. Importer certification
            Forms\Components\Section::make('16. Importer certification')->schema([
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\TextInput::make('importer_certification_signature')->label('Signature')->columnSpan(6),
                    Forms\Components\DatePicker::make('importer_certification_date')->label('Date')->native(false)->columnSpan(6),
                    Forms\Components\FileUpload::make('importer_signature_image_path')
                        ->label('Signature image (importer)')
                        ->image()->imageEditor(false)
                        ->disk('public')
                        ->directory('form6/signatures')
                        ->downloadable()
                        ->columnSpanFull(),
                ]),
            ]),

            // 17. Specific conditions
            Forms\Components\Section::make('17. Specific conditions')->schema([
                Forms\Components\Textarea::make('specific_conditions')->rows(2)->columnSpanFull(),
            ]),

            // Notes (placed after 17 and before Recovery Operations)
            Forms\Components\Section::make('Notes')->schema([
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->default('(1) Attach list, if more than one; (2) Select appropriate option; (3) Immediately contact competent authority in case of any\nemergency; (4) If more than one transporter carriers, attach information as required in SL. No. 12')
                    ->afterStateHydrated(function (Forms\Components\Textarea $component, $state) {
                        $fallback = '(1) Attach list, if more than one; (2) Select appropriate option; (3) Immediately contact competent authority in case of any\n'.
                                      'emergency; (4) If more than one transporter carriers, attach information as required in SL. No. 12';
                        if ($state === null || trim((string)$state) === '') {
                            $component->state($fallback);
                        }
                    })
                    ->columnSpanFull(),
            ]),

            // Recovery Operations (*)
            Forms\Components\Section::make('Recovery Operations (*)')->schema([
                Forms\Components\Repeater::make('recovery_operations')
                    ->label('Add Recovery Operation')
                    ->defaultItems(0)
                    ->columns(12)
                    ->schema([
                        Forms\Components\Select::make('code')
                            ->options([
                                'R1' => 'R1', 'R2' => 'R2', 'R3' => 'R3', 'R4' => 'R4', 'R5' => 'R5',
                                'R6' => 'R6', 'R7' => 'R7', 'R8' => 'R8', 'R9' => 'R9', 'R10' => 'R10', 'R11' => 'R11',
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $map = [
                                    'R1' => 'Use as a fuel (other than in direct incineration) or other means to generate energy.',
                                    'R2' => 'Solvent reclamation/regeneration.',
                                    'R3' => 'Recycling/reclamation of organic substances which are not used as solvents.',
                                    'R4' => 'Recycling/reclamation of metals and metal compounds.',
                                    'R5' => 'Recycling/reclamation of other inorganic materials.',
                                    'R6' => 'Regeneration of acids or bases.',
                                    'R7' => 'Recovery of components used for pollution abatement.',
                                    'R8' => 'Recovery of components from catalysts.',
                                    'R9' => 'Used oil re-refining or other reuses of previously used oil.',
                                    'R10' => 'Land treatment resulting in benefit to agriculture or ecological improvement',
                                    'R11' => 'Uses of residual materials obtained from any of the operations numbered R1 to R10',
                                ];
                                if ($state && isset($map[$state])) {
                                    $set('description', $map[$state]);
                                }
                            })
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->required()
                            ->columnSpan(10),
                    ])
                    ->addActionLabel('Add Recovery Operation')
                    ->reorderable()
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Notes & Signatures')->schema([
                Forms\Components\Textarea::make('terms_and_description')->label('Terms and description')->rows(3)->columnSpanFull(),
                Forms\Components\Group::make()->columns(12)->schema([
                    Forms\Components\TextInput::make('signature_place')->label('Place')->columnSpan(4),
                    Forms\Components\TextInput::make('signature_designation')->label('Designation')->columnSpan(4),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('F6 #')->sortable(),
            Tables\Columns\TextColumn::make('form6_no')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('form6_date')->date()->sortable(),
            Tables\Columns\TextColumn::make('invoice.invoice_no')->label('Invoice')->searchable(),
            Tables\Columns\TextColumn::make('shipment.booking_no')->label('BKG #')->searchable(),
            Tables\Columns\BadgeColumn::make('status')->colors([
                'gray' => 'draft',
                'info' => 'submitted',
                'success' => 'approved',
                'warning' => 'in_transit',
                'primary' => 'received',
                'secondary' => 'completed',
            ])->sortable(),
            Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn(Form6Document $record) => route('form6.pdf', ['form6' => $record->getKey(), 'download' => 0]))
                ->openUrlInNewTab(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForm6Documents::route('/'),
            'create' => Pages\CreateForm6Document::route('/create'),
            'edit' => Pages\EditForm6Document::route('/{record}/edit'),
        ];
    }
}
