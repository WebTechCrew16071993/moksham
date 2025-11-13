<?php

namespace App\Filament\Resources\Form6DocumentResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';
    protected static ?string $title = 'History';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('When')->since()->sortable(),
                Tables\Columns\BadgeColumn::make('action')->colors([
                    'info' => 'created',
                    'warning' => 'updated',
                    'success' => 'approved',
                    'gray' => 'draft',
                    'primary' => 'submitted',
                ])->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('By')->toggleable(),
                Tables\Columns\TextColumn::make('notes')->limit(60)->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('No history yet');
    }
}
