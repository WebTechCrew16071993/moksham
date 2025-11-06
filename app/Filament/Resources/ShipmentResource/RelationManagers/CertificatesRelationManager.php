<?php

namespace App\Filament\Resources\ShipmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CertificatesRelationManager extends RelationManager
{
    protected static string $relationship = 'certificatesOfOrigin';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('document_no')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('bl_no')->nullable(),
                Forms\Components\TextInput::make('port_of_loading')->nullable(),
                Forms\Components\TextInput::make('port_of_discharge')->nullable(),
                Forms\Components\TextInput::make('place_of_receipt')->nullable(),
                Forms\Components\TextInput::make('exporting_carrier')->nullable(),
                Forms\Components\TextInput::make('type_of_move')->nullable(),
                Forms\Components\Toggle::make('containerized')->default(true),
                Forms\Components\TextInput::make('total_packages')->numeric()->nullable(),
                Forms\Components\Textarea::make('description')->nullable(),
                Forms\Components\TextInput::make('total_gross_weight_kg')->numeric()->nullable(),
                Forms\Components\TextInput::make('total_bales')->numeric()->nullable(),
                Forms\Components\DatePicker::make('shipped_date')->native(false)->nullable(),
                Forms\Components\DatePicker::make('sworn_date')->native(false)->nullable(),
                Forms\Components\TextInput::make('owner_signature_path')->nullable(),
                Forms\Components\TextInput::make('chamber_signature_path')->nullable(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'finalized' => 'Finalized',
                    ])->native(false)->required(),
                Forms\Components\TextInput::make('pdf_path')->label('PDF Path')->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_no')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('bl_no')->toggleable(),
                Tables\Columns\TextColumn::make('port_of_loading')->toggleable(),
                Tables\Columns\TextColumn::make('port_of_discharge')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'gray' => 'draft',
                    'success' => 'finalized',
                ])->sortable(),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route('certificates.pdf', ['certificate' => $record->getKey(), 'download' => 0]))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
