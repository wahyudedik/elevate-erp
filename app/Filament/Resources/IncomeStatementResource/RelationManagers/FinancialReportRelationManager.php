<?php

namespace App\Filament\Resources\IncomeStatementResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FinancialReportRelationManager extends RelationManager
{
    protected static string $relationship = 'financialReport';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Laporan Keuangan')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->label('Cabang')
                            ->required(),
                        Forms\Components\TextInput::make('report_name')
                            ->label('Nama Laporan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('report_type')
                            ->label('Jenis Laporan')
                            ->options([
                                'balance_sheet' => 'Neraca',
                                'income_statement' => 'Laporan Laba Rugi',
                                'cash_flow' => 'Arus Kas',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('report_period_start')
                            ->label('Periode Awal')
                            ->default(now())
                            ->required(),
                        Forms\Components\DatePicker::make('report_period_end')
                            ->label('Periode Akhir')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->nullable()->columnSpan(2),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat pada')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir diubah')
                            ->content(fn($record): string => $record?->updated_at ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('financial_report_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary')
                    ->toggleable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('report_name')
                    ->label('Nama Laporan')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->size('sm')
                    ->weight('medium')
                    ->wrap(),
                Tables\Columns\TextColumn::make('report_type')
                    ->label('Jenis Laporan')
                    ->badge()
                    ->colors([
                        'primary' => 'balance_sheet',
                        'success' => 'income_statement',
                        'warning' => 'cash_flow',
                    ])
                    ->icons([
                        'balance_sheet' => 'heroicon-m-scale',
                        'income_statement' => 'heroicon-m-currency-dollar',
                        'cash_flow' => 'heroicon-m-arrow-path',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'balance_sheet' => 'Neraca',
                            'income_statement' => 'Laba Rugi',
                            'cash_flow' => 'Arus Kas',
                            default => $state,
                        };
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_period_start')
                    ->label('Periode Awal')
                    ->icon('heroicon-m-calendar')
                    ->iconColor('success')
                    ->date('d M Y')
                    ->toggleable()
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('report_period_end')
                    ->label('Periode Akhir')
                    ->date('d M Y')
                    ->toggleable()
                    ->icon('heroicon-m-calendar')
                    ->iconColor('success')
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(string $state): string => $state)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
            ])
            ->filters([
                Tables\Filters\Filter::make('report_period')
                    ->form([
                        Forms\Components\DatePicker::make('report_period_start')
                            ->label('Tanggal Mulai'),
                        Forms\Components\DatePicker::make('report_period_end')
                            ->label('Tanggal Selesai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['report_period_start'],
                                fn(Builder $query, $date): Builder => $query->whereDate('report_period_start', '>=', $date),
                            )
                            ->when(
                                $data['report_period_end'],
                                fn(Builder $query, $date): Builder => $query->whereDate('report_period_end', '<=', $date),
                            );
                    })->columns(2),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
