<?php

namespace App\Filament\Resources\LedgerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountRelationManager extends RelationManager
{
    protected static string $relationship = 'account';

    protected static ?string $title = 'Akun';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('account_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary')
                    ->searchable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Nama Akun')
                    ->toggleable()
                    ->sortable()
                    ->weight('medium')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('Nomor Akun')
                    ->toggleable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('account_type')
                    ->label('Tipe Akun')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'primary' => 'asset',
                        'danger' => 'liability',
                        'warning' => 'equity',
                        'success' => 'revenue',
                        'info' => 'expense',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'asset' => 'Aset',
                        'liability' => 'Kewajiban',
                        'equity' => 'Modal',
                        'revenue' => 'Pendapatan',
                        'expense' => 'Beban',
                    })
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('initial_balance')
                    ->label('Saldo Awal')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->alignment('right')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('current_balance')
                    ->label('Saldo Saat Ini')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->alignment('right')
                    ->weight('bold')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
