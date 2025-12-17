<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IndentResource\Pages;
use App\Models\HsnCode;
use App\Models\ImportCompany;
use App\Models\Indent;
use App\Services\DocumentPermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class IndentResource extends Resource
{
    protected static ?string $model = Indent::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Documents';

    protected static ?string $navigationLabel = 'Indents';

    protected static ?int $navigationSort = 10;

    protected static string $permissionResource = 'indents';

    public static function getModelLabel(): string
    {
        return 'Indent';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Indents';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Top: date and attention (each on its own line)
                Forms\Components\Group::make()
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('indent_no')
                            ->label('Indent No.')
                            ->numeric()
                            ->formatStateUsing(fn ($state, $record) => $record?->indent_no)
                            ->hidden() // hide in form as requested
                            ->dehydrated(false),
                        Forms\Components\DatePicker::make('indent_date')
                            ->label('Date')
                            ->default(now())
                            ->native(false)
                            ->required()
                            ->columnSpan(12),
                        Forms\Components\TextInput::make('kind_attention')
                            ->label('Kind Attn.')
                            ->placeholder('e.g. Shri Narottambhai Patel')
                            ->required()
                            ->columnSpan(12),
                    ]),

                // Party selectors (each on its own line)
                Forms\Components\Group::make()
                    ->columns(12)
                    ->schema([
                        Forms\Components\Select::make('consignee_id')
                            ->label('Consignee')
                            ->options(fn () => ImportCompany::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->hint('Select buyer/consignee')
                            ->required()
                            ->columnSpan(12),
                        Forms\Components\Select::make('hsn_code_id')
                            ->label('HS Code')
                            ->options(function () {
                                $query = HsnCode::query();
                                $user = \Illuminate\Support\Facades\Auth::user();
                                if ($user && !$user->isAdmin()) {
                                    $allowed = \App\Services\DocumentPermissionService::allowedCategoryIds($user);
                                    if (is_array($allowed)) {
                                        if (empty($allowed)) {
                                            return [];
                                        }
                                        $query->whereIn('category_id', $allowed);
                                    }
                                }
                                return $query->orderBy('code')->pluck('code', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->hint('Choose the HS code for the product')
                            ->required()
                            ->columnSpan(12),
                    ]),

                // Shipment details grid (each field one per line)
                Forms\Components\Group::make()
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('quality')
                            ->label('Quality')
                            ->placeholder('e.g. Wastepaper - OCC No: 11')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('origin')
                            ->placeholder('e.g. USA')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->placeholder('e.g. (10 CTNR) 215 MT (+/-10%)')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('moisture_and_throw')
                            ->label('Moisture & Throw')
                            ->placeholder('e.g. Moisture 12% & Out Throw 2%')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->prefix('US$')
                            ->placeholder('e.g. 200.00 PMT CIF MUNDRA')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('payment')
                            ->placeholder('e.g. 100% DP at Sight through bank')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('shipment_schedule')
                            ->label('Shipment Schedule')
                            ->placeholder('e.g. Immediate Shipment in 10 ctrn each')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('payload')
                            ->placeholder('e.g. Avg 21.5 MT per 40’ HC container')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('shipping_line')
                            ->placeholder('e.g. HAPAG')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('discharge_port')
                            ->placeholder('e.g. MUNDRA')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('final_destination')
                            ->placeholder('e.g. MUNDRA')
                            ->columnSpan(12)
                            ->required(),
                        Forms\Components\TextInput::make('release_type_of_obl')
                            ->label('Release type of OBL')
                            ->placeholder('e.g. OBL')
                            ->columnSpan(12)
                            ->required(),
                    ]),

                // Rich text clauses with compact toolbars
                Forms\Components\Group::make()
                    ->columns(12)
                    ->schema([
                        Forms\Components\RichEditor::make('other_terms')
                            ->label('Other Terms')
                            ->default('<ol>'
                                .'<li>This Agreement shall be governed by and construed in accordance with the laws of United States of America.</li>'
                                .'<li>All amounts and commercial terms as specified in this indent shall prevail unless mutually amended in writing.</li>'
                                .'</ol>')
                            ->columnSpan(12)
                            ->disableToolbarButtons(['attachFiles','codeBlock'])
                            ->required(),
                        Forms\Components\RichEditor::make('claims')
                            ->label('Claims')
                            ->default('<ol>'
                                .'<li>In case of short weight claim, the claims should be forwarded in writing along with weightment slips of loaded container and empty container chassis of the truck. Note that weightment slips must be of independent weigh bridge and not mills weigh bridge.</li>'
                                .'<li>If there is any delay for any reason from the shipping line supplier / Shipper will held no responsible.</li>'
                                ."<li>For any moisture or out throw claim we will need unloading pics with the container numbers displayed and bales coming out of the container.</li>"
                                ."<li>If there is no evidence then there would be if shipping line splits the container after loading supplier / shipper will not be responsible.</li>"
                                ."<li>For weight short we will need port computerized weight slips and mill weight slips to check the shortage.</li>"
                                ."<li>Any additional War Risk surcharge or any such levies imposed by shipping company will be to buyers account.</li>"
                                ."<li>All agreements are contingent upon strikes, lock outs, delay of the carrier or other delay sun avoidable or beyond our control.</li>"
                                ."<li>Any claim discrepancy shall be adjusted on the next purchase order invoice.</li>"
                                ."<li>Shipments must be executed within 40 days from the date of the indent.</li>"
                                ."<li>This Agreement shall be governed by and construed in accordance with the laws of United States of America.</li>"
                                .'</ol>')
                            ->columnSpan(12)
                            ->disableToolbarButtons(['attachFiles','codeBlock'])
                            ->required(),
                        Forms\Components\RichEditor::make('remarks')
                            ->label('Remarks')
                            ->default('<ol>'
                                .'<li>This contract is between shipper MOKSHAM EXPORT IMPORT LLC and Buyer.</li>'
                                .'<li>In case of any variation in material quality, kindly ensure to take detailed container unloading pictures and the moisture meter reading pictures in case of any moisture.</li>'
                                .'</ol>')
                            ->columnSpan(12)
                            ->disableToolbarButtons(['attachFiles','codeBlock'])
                            ->required(),
                    ]),

                // Status section (visible on edit only)
                Forms\Components\Section::make('Document Status')
                    ->hiddenOn(['create'])
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'generated' => 'Generated (Shipper signed)',
                                'sent_to_consignee' => 'Sent to Consignee',
                                'signed_by_consignee' => 'Signed by Consignee (awaiting upload)',
                                'completed' => 'Completed',
                            ])
                            ->native(false)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                // Forms\Components\Section::make('Consignee Signature')
                //     ->schema([
                //         Forms\Components\FileUpload::make('consignee_signature_path')
                //             ->label('Consignee Signature')
                //             ->directory('signatures/consignee')
                //             ->image()
                //             ->visibility('public')
                //             ->hiddenOn(['create'])
                //             ->downloadable()
                //             ->openable(),
                //     ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('indent_no')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('indent_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('consignee_name')->label('Consignee')->searchable(),
                Tables\Columns\TextColumn::make('hsn_code')->label('HS Code')->searchable(),
                Tables\Columns\TextColumn::make('price')->label('Price'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'generated',
                        'warning' => 'sent_to_consignee',
                        'primary' => 'signed_by_consignee',
                        'success' => 'completed',
                    ])
                    ->label('Status')
                    ->formatStateUsing(fn (string $state) => Str::of($state)->replace('_', ' ')->title())
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'generated' => 'Generated (Shipper signed)',
                        'sent_to_consignee' => 'Sent to Consignee',
                        'signed_by_consignee' => 'Signed by Consignee',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(function (Indent $record) {
                        $hasShipment = \App\Models\Shipment::query()->where('indent_id', $record->getKey())->exists();
                        return !$hasShipment;
                    }),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Indent $record) => route('indents.print', $record))
                    ->openUrlInNewTab(),
                // Tables\Actions\Action::make('download_pdf')
                //     ->label('Download PDF')
                //     ->icon('heroicon-o-document-arrow-down')
                //     ->url(fn (Indent $record) => route('indents.print', ['indent' => $record->getKey(), 'download' => 1]))
                //     ->openUrlInNewTab(),
                Tables\Actions\Action::make('upload_signed_pdf')
                    ->label('Upload Signed PDF')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->visible(fn (Indent $record) => $record->status === 'signed_by_consignee')
                    ->form([
                        Forms\Components\FileUpload::make('signed_pdf')
                            ->label('Signed PDF (Consignee)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->required()
                            ->storeFiles(false),
                    ])
                    ->action(function (Indent $record, array $data) {
                        /** @var TemporaryUploadedFile|string|null $file */
                        $file = $data['signed_pdf'] ?? null;
                        if ($file instanceof TemporaryUploadedFile) {
                            $filename = $record->indent_no . '_consignee.pdf';
                            $path = 'indents/' . $filename;
                            Storage::disk('public')->putFileAs('indents', $file, $filename);
                            $record->consignee_signed_pdf_path = $path;
                            $record->status = 'completed';
                            $record->save();
                        }
                    })
                    ->successNotificationTitle('Signed PDF uploaded and status marked as completed'),
                Tables\Actions\Action::make('download_consignee_signed_pdf')
                    ->label('Download Signed PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn (Indent $record) => filled($record->consignee_signed_pdf_path))
                    ->url(fn (Indent $record) => asset('storage/'.$record->consignee_signed_pdf_path))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIndents::route('/'),
            'create' => Pages\CreateIndent::route('/create'),
            'edit' => Pages\EditIndent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();
        if ($user && !$user->isAdmin()) {
            $allowed = \App\Services\DocumentPermissionService::allowedCategoryIds($user);
            if (is_array($allowed)) {
                if (empty($allowed)) {
                    // No categories assigned -> show no records
                    return $query->whereRaw('1 = 0');
                }
                $query = $query->whereHas('hsn', function ($q) use ($allowed) {
                    $q->whereIn('category_id', $allowed);
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
}
