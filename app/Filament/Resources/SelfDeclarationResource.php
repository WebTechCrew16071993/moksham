<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SelfDeclarationResource\Pages;
use App\Models\SelfDeclaration;
use App\Models\Invoice;
use App\Services\DocumentPermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class SelfDeclarationResource extends Resource
{
    protected static ?string $model = SelfDeclaration::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?string $navigationLabel = 'Self-Declaration';
    protected static ?int $navigationSort = 27;

    protected static string $permissionResource = 'self_declaration';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columns(12)->schema([
                Forms\Components\Select::make('invoice_id')
                    ->label('Invoice')
                    ->options(function(){
                        $query = Invoice::query()->latest('id');
                        $user = Auth::user();
                        if ($user && !$user->isAdmin()) {
                            $allowed = \App\Services\DocumentPermissionService::allowedCategoryIds($user);
                            if (is_array($allowed)) {
                                if (empty($allowed)) return [];
                                $query->whereHas('shipment.indent.hsn', fn($q)=> $q->whereIn('category_id', $allowed));
                            }
                        }
                        return $query->pluck('invoice_no','id');
                    })
                    ->searchable()->preload()->native(false)->required()->columnSpan(3),
                Forms\Components\TextInput::make('certificate_number')->label('Certificate #')->readOnly()->columnSpan(3),
                Forms\Components\DatePicker::make('issue_date')->label('Issue Date')->native(false)->columnSpan(3),
                Forms\Components\TextInput::make('invoice_no')->label('Invoice No')->readOnly()->columnSpan(3),
                Forms\Components\Textarea::make('importer_details')->rows(3)->columnSpan(6),
                Forms\Components\TextInput::make('goods_description')->columnSpan(3),
                Forms\Components\TextInput::make('total_quantity_kgs')->numeric()->columnSpan(3),
                Forms\Components\Textarea::make('declaration_points')->rows(4)->columnSpan(12)
                    ->dehydrateStateUsing(fn($state)=> is_array($state)? $state : null)
                    ->afterStateHydrated(function($component,$state){ if(is_array($state)) $component->state(json_encode($state, JSON_PRETTY_PRINT)); }),
                Forms\Components\TextInput::make('signer_name')->columnSpan(6),
                Forms\Components\TextInput::make('signer_title')->columnSpan(6),
                Forms\Components\Select::make('status')->options([
                    'draft'=>'Draft','finalized'=>'Finalized'
                ])->default('draft')->native(false)->columnSpan(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('certificate_number')->label('Certificate #')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('issue_date')->label('Date')->date()->sortable(),
            Tables\Columns\TextColumn::make('invoice.invoice_no')->label('Invoice')->searchable(),
            Tables\Columns\TextColumn::make('total_quantity_kgs')->label('Qty (Kgs)')->sortable(),
            Tables\Columns\BadgeColumn::make('status')->colors([
                'gray'=>'draft','success'=>'finalized'
            ])->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn(SelfDeclaration $record) => route('self_declaration.pdf', ['doc'=>$record->getKey(),'download'=>0]))
                ->openUrlInNewTab(),
            // Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSelfDeclarations::route('/'),
            'create' => Pages\CreateSelfDeclaration::route('/create'),
            'edit' => Pages\EditSelfDeclaration::route('/{record}/edit'),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        if ($user && !$user->isAdmin()) {
            $allowed = DocumentPermissionService::allowedCategoryIds($user);
            if (is_array($allowed)) {
                if (empty($allowed)) return $query->whereRaw('1 = 0');
                $query = $query->whereHas('invoice.shipment.indent.hsn', function ($q) use ($allowed) {
                    $q->whereIn('category_id', $allowed);
                });
            }
        }
        return $query;
    }
}
