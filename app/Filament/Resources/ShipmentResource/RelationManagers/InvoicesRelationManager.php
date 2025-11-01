<?php

namespace App\Filament\Resources\ShipmentResource\RelationManagers;

use App\Models\ImportCompany;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_no')->required()->unique(ignoreRecord: true),
                Forms\Components\DatePicker::make('date')->native(false)->required(),
                Forms\Components\TextInput::make('employee')->required(),
                Forms\Components\TextInput::make('obl_ref')->nullable(),
                Forms\Components\TextInput::make('no_of_cont')->nullable(),
                Forms\Components\TextInput::make('weight_mt')->numeric()->nullable(),
                Forms\Components\Textarea::make('description')->nullable(),
                Forms\Components\TextInput::make('moisture_contain')->label('Moisture Contain')->nullable(),
                Forms\Components\TextInput::make('rate_mt')->numeric()->nullable(),
                Forms\Components\TextInput::make('amount')->numeric()->nullable(),
                Forms\Components\TextInput::make('payment_terms')->nullable(),
                Forms\Components\TextInput::make('advance')->numeric()->default(0),
                Forms\Components\TextInput::make('amount_due')->numeric()->nullable(),
                Forms\Components\DatePicker::make('return_date')->native(false)->nullable(),
                Forms\Components\DatePicker::make('cut_off_date')->native(false)->nullable(),
                Forms\Components\DatePicker::make('dept_est')->native(false)->nullable(),
                Forms\Components\DatePicker::make('arrival')->native(false)->nullable(),
                Forms\Components\DatePicker::make('si_cut_off')->native(false)->nullable(),
                Forms\Components\TextInput::make('f_dest')->label('Final Destination')->nullable(),
                Forms\Components\RichEditor::make('remarks')->nullable(),
                Forms\Components\RichEditor::make('terms_conditions')->nullable(),
                Forms\Components\Select::make('consignee_id')
                    ->label('Bill To (Consignee)')
                    ->options(fn () => ImportCompany::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'finalized' => 'Finalized',
                        'sent' => 'Sent',
                        'paid' => 'Paid',
                    ])->native(false)->required(),
                Forms\Components\TextInput::make('pdf_path')->label('PDF Path')->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('consignee.name')->label('Bill To')->searchable(),
                Tables\Columns\TextColumn::make('amount')->money('usd', true)->sortable(),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'gray' => 'draft',
                    'warning' => 'sent',
                    'success' => 'paid',
                    'info' => 'finalized',
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
