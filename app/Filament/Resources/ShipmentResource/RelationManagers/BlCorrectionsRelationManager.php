<?php

namespace App\Filament\Resources\ShipmentResource\RelationManagers;

use App\Models\PackingList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BlCorrectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'blCorrections';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('packing_list_id')
                    ->label('Packing List')
                    ->options(function (): array {
                        $owner = $this->getOwnerRecord();
                        return $owner
                            ? PackingList::query()
                                ->where('shipment_id', $owner->getKey())
                                ->orderByDesc('id')
                                ->pluck('id', 'id')
                                ->toArray()
                            : [];
                    })
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                Forms\Components\DatePicker::make('bl_date')->label('BL Date')->native(false)->required(),
                Forms\Components\TextInput::make('destination')->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_review' => 'Pending Review',
                        'corrections_requested' => 'Corrections Requested',
                        'approved' => 'Approved',
                        'finalized' => 'Finalized',
                    ])
                    ->native(false)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('BL #')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('packing_list_id')->label('PL #')->sortable(),
                Tables\Columns\TextColumn::make('destination')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('bl_date')->date()->sortable(),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'gray' => 'draft',
                    'warning' => 'pending_review',
                    'danger' => 'corrections_requested',
                    'success' => 'approved',
                    'primary' => 'finalized',
                ])->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
