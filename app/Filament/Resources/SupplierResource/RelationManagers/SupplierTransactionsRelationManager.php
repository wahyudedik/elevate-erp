<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class SupplierTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'supplierTransactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('Enter transaction code')
                    ->label('Transaction Code'),
                Forms\Components\Select::make('transaction_type')
                    ->required()
                    ->options([
                        'purchase_order' => 'Purchase Order',
                        'payment' => 'Payment',
                        'refund' => 'Refund',
                    ])
                    ->label('Transaction Type'),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('IDR')
                    ->placeholder('Enter amount')
                    ->label('Amount'),
                Forms\Components\DatePicker::make('transaction_date')
                    ->required()
                    ->maxDate(now())
                    ->label('Transaction Date'),
                Forms\Components\Textarea::make('notes')
                    ->nullable()
                    ->placeholder('Enter additional notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('supplier_id')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->label('Transaction Code'),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->colors([
                        'primary' => 'purchase_order',
                        'success' => 'payment',
                        'danger' => 'refund',
                    ])
                    ->searchable()
                    ->sortable()
                    ->label('Transaction Type'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable()
                    ->label('Amount'),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable()
                    ->label('Transaction Date'),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Notes'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created At'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Updated At'),
            ])
            ->filters([
                //
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
