<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
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
use Illuminate\Validation\Rule;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?int $navigationSort = 29;

    public static function getModelLabel(): string
    {
        return 'Category';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Categories';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->rule(fn (string $operation, $record) =>
                                Rule::unique('categories', 'name')
                                    ->whereNull('deleted_at')
                                    ->ignore($record?->id))
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (\App\Models\Category $record, Tables\Actions\DeleteAction $action) {
                        if (method_exists($record, 'hsnCodes') && $record->hsnCodes()->exists()) {
                            Notification::make()
                                ->title('Cannot delete Category')
                                ->body('This category is used by one or more HSN codes and cannot be deleted.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        // Keep default SoftDeletingScope so trashed categories are hidden by default
        return parent::getEloquentQuery();
    }
}
