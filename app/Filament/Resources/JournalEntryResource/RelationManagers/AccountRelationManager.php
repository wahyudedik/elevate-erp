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
                Forms\Components\Section::make('Informasi Akun')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih cabang tempat akun ini berada'),
                        Forms\Components\TextInput::make('account_name')
                            ->label('Nama Akun')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama akun')
                            ->helperText('Contoh: Kas Bank, Piutang Usaha, dll'),
                        Forms\Components\TextInput::make('account_number')
                            ->label('Nomor Akun')
                            ->required()
                            ->unique(ignorable: fn($record) => $record)
                            ->maxLength(255)
                            ->placeholder('Masukkan nomor akun')
                            ->helperText('Masukkan nomor akun sesuai dengan kode akuntansi'),
                        Forms\Components\Select::make('account_type')
                            ->label('Tipe Akun')
                            ->required()
                            ->options([
                                'asset' => 'Aset (Asset)',
                                'liability' => 'Kewajiban (Liability)',
                                'equity' => 'Modal (Equity)',
                                'revenue' => 'Pendapatan (Revenue)',
                                'expense' => 'Pengeluaran (Expense)',
                            ])
                            ->helperText('Pilih tipe akun sesuai kategori')
                            ->native(false),
                        Forms\Components\TextInput::make('initial_balance')
                            ->label('Saldo Awal')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->maxValue(999999999999999.99)
                            ->default(0)
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('current_balance', $state);
                            })
                            ->helperText('Masukkan saldo awal akun'),
                        Forms\Components\TextInput::make('current_balance')
                            ->label('Saldo Saat Ini')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->maxValue(999999999999999.99)
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('initial_balance', $state);
                            })
                            ->helperText('Saldo terkini dari akun ini'),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-document-duplicate')
                    ->description('Lengkapi informasi detail akun di bawah ini'),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat Pada')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->content(fn($record): string => $record?->updated_at ? $record->updated_at->diffForHumans() : '-')
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->icon('heroicon-o-information-circle')
                    ->description('Informasi waktu pembuatan dan pembaruan data'),
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
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-building-office-2')
                    ->iconColor('primary')
                    ->sortable()
                    ->alignLeft()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Nama Akun')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->alignLeft()
                    ->weight('medium')
                    ->iconColor('success'),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('Nomor Akun')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->alignLeft()
                    ->icon('heroicon-o-hashtag')
                    ->iconColor('gray'),
                Tables\Columns\TextColumn::make('account_type')
                    ->label('Kategori Akun')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'success' => 'asset',
                        'danger' => 'liability',
                        'warning' => 'equity',
                        'primary' => 'revenue',
                        'info' => 'expense',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'asset' => 'Aset',
                        'liability' => 'Liabilitas',
                        'equity' => 'Ekuitas',
                        'revenue' => 'Pendapatan',
                        'expense' => 'Pengeluaran',
                    })
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('initial_balance')
                    ->label('Saldo Awal')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->alignRight()
                    ->weight('bold')
                    ->icon('heroicon-o-banknotes')
                    ->iconColor('success'),
                Tables\Columns\TextColumn::make('current_balance')
                    ->label('Saldo Saat Ini')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->alignRight()
                    ->weight('bold')
                    ->icon('heroicon-o-calculator')
                    ->iconColor('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pembuatan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tanggal Pembaruan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter()
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
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
