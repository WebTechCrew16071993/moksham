<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HsnCodeResource\Pages;
use App\Models\HsnCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class HsnCodeResource extends Resource
{
    protected static ?string $model = HsnCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'HSN Codes';

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return 'HSN Code';
    }

    public static function getPluralModelLabel(): string
    {
        return 'HSN Codes';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->label('Category')
                            ->options([
                                'Plastic' => 'Plastic',
                                'Metal' => 'Metal',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('code')
                            ->label('HSN Code')
                            ->placeholder('e.g. 3920')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(20)
                            ->rule('regex:/^\\d{4,8}$/')
                            ->validationMessages([
                                'regex' => 'HSN Code must be 4 to 8 digits.',
                            ])
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('HSN Code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->sortable()->badge(),
                // Tables\Columns\TextColumn::make('description')->limit(50)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            // ->filters([
            //     TrashedFilter::make(),
            //     Tables\Filters\SelectFilter::make('category')
            //         ->options([
            //             'Plastic' => 'Plastic',
            //             'Metal' => 'Metal',
            //         ]),
            //     Tables\Filters\TernaryFilter::make('is_active')
            //         ->label('Active')
            //         ->boolean(),
            // ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (HsnCode $record, Tables\Actions\DeleteAction $action) {
                        // Prevent delete if referenced by any indent
                        if (method_exists($record, 'indents') && $record->indents()->exists()) {
                            Notification::make()
                                ->title('Cannot delete HSN Code')
                                ->body('This HSN code is used in one or more Indents and cannot be deleted.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //         Tables\Actions\ForceDeleteBulkAction::make(),
            //         Tables\Actions\RestoreBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHsnCodes::route('/'),
            'create' => Pages\CreateHsnCode::route('/create'),
            'edit' => Pages\EditHsnCode::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}

