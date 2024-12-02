<?php

namespace App\Filament\Resources\CashFlowResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class FinancialReportRelationManager extends RelationManager
{
    protected static string $relationship = 'financialReport';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Laporan Keuangan')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->required()
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Cabang'),
                        Forms\Components\TextInput::make('report_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Laporan'),
                        Forms\Components\Select::make('report_type')
                            ->options([
                                'balance_sheet' => 'Neraca',
                                'income_statement' => 'Laporan Laba Rugi',
                                'cash_flow' => 'Arus Kas',
                            ])
                            ->required()
                            ->label('Jenis Laporan'),
                        Forms\Components\DatePicker::make('report_period_start')
                            ->default(now())
                            ->required()
                            ->label('Periode Awal'),
                        Forms\Components\DatePicker::make('report_period_end')
                            ->required()
                            ->label('Periode Akhir'),
                        Forms\Components\Textarea::make('notes')
                            ->nullable()->columnSpan(2)
                            ->label('Catatan'),
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
                    ->searchable()
                    ->sortable()
                    ->label('Cabang')
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary')
                    ->toggleable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('report_name')
                    ->searchable()
                    ->toggleable()
                    ->label('Nama Laporan')
                    ->sortable()
                    ->size('sm')
                    ->weight('medium')
                    ->icon('heroicon-o-document-text')
                    ->iconColor('success'),
                Tables\Columns\TextColumn::make('report_type')
                    ->label('Jenis Laporan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'balance_sheet' => 'info',
                        'income_statement' => 'success',
                        'cash_flow' => 'warning',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'balance_sheet' => 'Neraca',
                        'income_statement' => 'Laba Rugi',
                        'cash_flow' => 'Arus Kas',
                    })
                    ->icons([
                        'balance_sheet' => 'heroicon-o-scale',
                        'income_statement' => 'heroicon-o-currency-dollar',
                        'cash_flow' => 'heroicon-o-arrow-path',
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_period_start')
                    ->date()
                    ->label('Periode Awal')
                    ->toggleable()
                    ->sortable()
                    ->size('sm')
                    ->icon('heroicon-o-calendar')
                    ->iconColor('gray'),
                Tables\Columns\TextColumn::make('report_period_end')
                    ->date()
                    ->label('Periode Akhir')
                    ->toggleable()
                    ->sortable()
                    ->size('sm')
                    ->icon('heroicon-o-calendar')
                    ->iconColor('gray'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(string $state): string => $state)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->color('gray')
            ])
            ->filters([
                Tables\Filters\filter::make('report_period')
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
                ViewAction::make(),
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
