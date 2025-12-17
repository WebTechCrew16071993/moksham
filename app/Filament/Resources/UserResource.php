<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 40;

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\Section::make('User Info')
    //                 ->schema([
    //                     Forms\Components\TextInput::make('name')
    //                         ->required()
    //                         ->maxLength(255),
    //                     Forms\Components\TextInput::make('email')
    //                         ->email()
    //                         ->required()
    //                         ->unique(ignoreRecord: true)
    //                         ->maxLength(255),
    //                     Forms\Components\Select::make('role')
    //                         ->options([
    //                             'admin' => 'Admin',
    //                             'user' => 'User',
    //                         ])
    //                         ->required()
    //                         ->native(false)
    //                         ->reactive(),
    //                     Forms\Components\TextInput::make('password')
    //                         ->password()
    //                         ->required(fn (string $operation): bool => $operation === 'create')
    //                         ->dehydrated(fn ($state) => filled($state))
    //                         ->dehydrateStateUsing(fn ($state) => Hash::make($state))
    //                         ->revealable()
    //                         ->visible(fn (string $operation): bool => $operation === 'create'),
    //                     Forms\Components\TextInput::make('password_confirmation')
    //                         ->password()
    //                         ->required(fn (string $operation): bool => $operation === 'create')
    //                         ->same('password')
    //                         ->revealable()
    //                         ->dehydrated(false)
    //                         ->visible(fn (string $operation): bool => $operation === 'create'),
    //                     Forms\Components\Toggle::make('status')
    //                         ->label('Active')
    //                         ->default(true),
    //                 ])->columns(2),

    //             Forms\Components\Section::make('Permissions')
    //                 ->visible(fn (Forms\Get $get): bool => ($get('role') === 'user'))
    //                 ->extraAttributes(['class' => 'mt-6'])
    //                 ->schema([
    //                     Forms\Components\Repeater::make('permissions')
    //                         ->dehydrated(false)
    //                         ->default(fn () => static::permissionDefaults())
    //                         ->reorderable(false)
    //                         ->addable(false)
    //                         ->deletable(false)
    //                         ->collapsed(false)
    //                         ->collapsible(false)
    //                         ->itemLabel(null)
    //                                 ->columns(12)
    //                         ->schema([
    //                             Forms\Components\Grid::make(12)
    //                                 ->extraAttributes(['class' => 'items-center gap-x-12'])
    //                                 ->schema([
    //                                     Forms\Components\Hidden::make('resource'),
    //                                     Forms\Components\Placeholder::make('resource_name')
    //                                         ->hiddenLabel()
    //                                         ->content(fn (Forms\Get $get): ?string => static::permissionResources()[$get('resource')] ?? ($get('resource') ?? null))
    //                                         ->extraAttributes(['class' => 'pr-6'])
    //                                         ->columnSpan(6),
    //                                     Forms\Components\Toggle::make('can_view')->label('View')->inline(true)->extraAttributes(['class' => 'pr-8'])->columnSpan(2),
    //                                     Forms\Components\Toggle::make('can_create')->label('Add')->inline(true)->extraAttributes(['class' => 'pl-6 pr-8'])->columnSpan(1),
    //                                     Forms\Components\Toggle::make('can_update')->label('Edit')->inline(true)->extraAttributes(['class' => 'pl-6 pr-8'])->columnSpan(1),
    //                                     Forms\Components\Toggle::make('can_delete')->label('Delete')->inline(true)->extraAttributes(['class' => 'pl-6'])->columnSpan(2),
    //                                 ]),
    //                         ]),
    //                 ]),
    //         ]);
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // -----------------------------
                // USER INFO
                // -----------------------------
                Forms\Components\Section::make('User Info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        // Show read-only Admin label when the record is an Admin
                        Forms\Components\Placeholder::make('__role_admin_label')
                            ->label('Role')
                            ->content('Admin')
                            ->visible(fn (Forms\Get $get): bool => ($get('role') === 'admin'))
                            ->dehydrated(false),

                        // For non-admin records and on create, allow only 'User'
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options([ 'user' => 'User' ])
                            ->default('user')
                            ->required()
                            ->native(false)
                            ->visible(fn (Forms\Get $get): bool => ($get('role') !== 'admin')),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->revealable()
                            ->visible(fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->same('password')
                            ->revealable()
                            ->dehydrated(false)
                            ->visible(fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\Toggle::make('status')
                            ->label('Active')
                            ->default(true),

                        Forms\Components\Select::make('categories')
                            ->label('Categories')
                            ->relationship('categories', 'name', modifyQueryUsing: function ($query) {
                                // Avoid ambiguous deleted_at when joining pivot that also has deleted_at
                                return $query
                                    ->withoutGlobalScopes([SoftDeletingScope::class])
                                    ->whereNull('categories.deleted_at');
                            })
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->visible(fn (Forms\Get $get): bool => ($get('role') === 'user'))
                            ->required(fn (Forms\Get $get): bool => ($get('role') === 'user'))
                            ->helperText('Visible and manageable documents will be restricted to these categories for this user.'),
                    ])->columns(2),

                // -----------------------------
                // PERMISSIONS
                // -----------------------------
                Forms\Components\Section::make('Permissions')
                    ->visible(fn (Forms\Get $get): bool => ($get('role') === 'user'))
                    ->extraAttributes(['class' => 'mt-6'])
                    ->headerActions([

                        // ----- Select All Permissions -----
                        Forms\Components\Actions\Action::make('select_all_permissions')
                            ->label('Select All Permissions')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                $permissions = $get('permissions') ?? [];

                                // Check if all selected
                                $allSelected = collect($permissions)->every(fn ($p) =>
                                    ($p['can_view'] ?? false) &&
                                    ($p['can_create'] ?? false) &&
                                    ($p['can_update'] ?? false) &&
                                    ($p['can_delete'] ?? false)
                                );

                                $new = !$allSelected;

                                foreach ($permissions as $index => $permission) {
                                    $set("permissions.{$index}.can_view", $new);
                                    $set("permissions.{$index}.can_create", $new);
                                    $set("permissions.{$index}.can_update", $new);
                                    $set("permissions.{$index}.can_delete", $new);
                                }
                            }),

                        // ----- Clear All -----
                        Forms\Components\Actions\Action::make('clear_all_permissions')
                            ->label('Clear All')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                $permissions = $get('permissions') ?? [];

                                foreach ($permissions as $index => $permission) {
                                    $set("permissions.{$index}.can_view", false);
                                    $set("permissions.{$index}.can_create", false);
                                    $set("permissions.{$index}.can_update", false);
                                    $set("permissions.{$index}.can_delete", false);
                                }
                            }),
                    ])

                    ->schema([

                        // Header labels for the permissions columns
                        Forms\Components\Grid::make(21)
                            ->extraAttributes(['class' => 'px-2 pb-2 text-xs font-medium text-gray-500 select-none'])
                            ->schema([
                                Forms\Components\Placeholder::make('')->columnSpan(4),
                                Forms\Components\Placeholder::make('view')->columnSpan(4)->extraAttributes(['class' => 'text-center']),
                                Forms\Components\Placeholder::make('add')->columnSpan(4)->extraAttributes(['class' => 'text-center']),
                                Forms\Components\Placeholder::make('edit')->columnSpan(4)->extraAttributes(['class' => 'text-center']),
                                Forms\Components\Placeholder::make('delete')->columnSpan(4)->extraAttributes(['class' => 'text-center']),
                                // Forms\Components\Placeholder::make('all')->columnSpan(1)->extraAttributes(['class' => 'text-right pr-1']),
                            ]),

                        // ------------------------------------------------
                        // PERMISSION REPEATER (NO HEADER)
                        // ------------------------------------------------
                        Forms\Components\Repeater::make('permissions')
                            ->dehydrated(false)
                            ->default(fn () => static::permissionDefaults())
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false)
                            ->collapsed(false)
                            ->collapsible(false)
                            ->itemLabel(null)
                            ->columns(21)

                            ->schema([
                                Forms\Components\Hidden::make('resource'),

                                // Resource Name
                                Forms\Components\Placeholder::make('resource_name')
                                    ->hiddenLabel()
                                    ->content(fn (Forms\Get $get): ?string =>
                                        static::permissionResources()[$get('resource')] ?? $get('resource')
                                    )
                                    ->extraAttributes(['class' => 'font-semibold text-gray-700'])
                                    ->columnSpan(4),

                                // ----- VIEW -----
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Toggle::make('can_view')->hiddenLabel(),
                                    ])
                                    ->columnSpan(4),

                                // ----- ADD -----
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Toggle::make('can_create')->hiddenLabel(),
                                    ])
                                    ->columnSpan(4),

                                // ----- UPDATE -----
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Toggle::make('can_update')->hiddenLabel(),
                                    ])
                                    ->columnSpan(4),

                                // ----- DELETE -----
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Toggle::make('can_delete')->hiddenLabel(),
                                    ])
                                    ->columnSpan(4),

                                // ----- Row All Button -----
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('select_all_row')
                                        ->label('All')
                                        ->link()
                                        ->size('xs')
                                        ->color('primary')
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $all = $get('can_view') &&
                                                $get('can_create') &&
                                                $get('can_update') &&
                                                $get('can_delete');

                                            $new = !$all;

                                            $set('can_view', $new);
                                            $set('can_create', $new);
                                            $set('can_update', $new);
                                            $set('can_delete', $new);
                                        }),
                                ])
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'success' => 'admin',
                        'secondary' => 'user',
                    ])
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->label('Deleted')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ]),
                TernaryFilter::make('status')
                    ->label('Active')
                    ->default(null),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Allow managing soft-deleted records via TrashedFilter
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('role', 'user');
    }

    // Restrict access to admins only
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        // Prevent self-deletion to avoid locking out the sole admin
        $user = Auth::user();
        if ($record instanceof User && $user && $record->id === $user->id) {
            return false;
        }
        return static::canViewAny();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function permissionResources(): array
    {
        return [
            'indents' => 'Indents',
            'shipments' => 'Shipments',
            'packing_lists' => 'Packing Lists',
            'bls' => 'BLs',
            'invoices' => 'Invoices',
            'form6' => 'Form 6',
            'form9' => 'Form 9',
        ];
    }

    public static function permissionDefaults(): array
    {
        return collect(static::permissionResources())
            ->keys()
            ->map(fn ($key) => [
                'resource' => $key,
                'can_view' => false,
                'can_create' => false,
                'can_update' => false,
                'can_delete' => false,
            ])->all();
    }
}
