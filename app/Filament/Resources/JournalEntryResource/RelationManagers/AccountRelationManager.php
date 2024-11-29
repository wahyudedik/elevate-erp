<?php

namespace App\Filament\Resources\JournalEntryResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AccountRelationManager extends RelationManager
{
    protected static string $relationship = 'account';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Infomasi Akun')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('account_name')
                            ->label('Nama Akun')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_number')
                            ->label('Nomor Akun')
                            ->required()
                            ->unique(ignorable: fn($record) => $record)
                            ->maxLength(255),
                        Forms\Components\Select::make('account_type')
                            ->label('Tipe Akun')
                            ->required()
                            ->options([
                                'asset' => 'Asset / Aset',
                                'liability' => 'Liability / Kewajiban',
                                'equity' => 'Equity / Modal',
                                'revenue' => 'Revenue / Pendapatan',
                                'expense' => 'Expense / Pengeluaran',
                            ]),
                        Forms\Components\TextInput::make('initial_balance')
                            ->label('Saldo Awal')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->default(0)
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('current_balance', $state);
                            }),
                        Forms\Components\TextInput::make('current_balance')
                            ->label('Saldo Saat Ini')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('initial_balance', $state);
                            }),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat pada')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir diubah pada')
                            ->content(fn($record): string => $record?->updated_at ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('account_name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-building-storefront')
                    ->sortable()
                    ->alignLeft(),
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Nama Akun')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->alignLeft(),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('Nomor Akun')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->alignLeft(),
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
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('initial_balance')
                    ->label('Saldo Awal')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->label('Saldo Saat Ini')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter()
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
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
