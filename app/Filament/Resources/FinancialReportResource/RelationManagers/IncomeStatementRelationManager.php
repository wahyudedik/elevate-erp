<?php

namespace App\Filament\Resources\FinancialReportResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\IncomeStatementExporter;
use App\Models\ManagementFinancial\FinancialReport;
use App\Models\ManagementFinancial\IncomeStatement;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class IncomeStatementRelationManager extends RelationManager
{
    protected static string $relationship = 'incomeStatement';

    protected static ?string $title = 'Laba Rugi';

    protected static ?string $label = 'Laba Rugi';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Laba Rugi')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->required()
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'Active'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('total_revenue')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->label('Total Pendapatan'),
                        Forms\Components\TextInput::make('total_expenses')
                            ->required()
                            ->default(0)
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->label('Total Pengeluaran'),
                        Forms\Components\TextInput::make('net_income')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->label('Pendapatan Bersih'),
                    ])
                    ->columns(2)
                    ->collapsible(),
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
            ->recordTitleAttribute('financial_report_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->size('sm')
                    ->badge(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->iconColor('primary')
                    ->size('sm')
                    ->weight('medium')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Pendapatan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('success')
                    ->size('sm')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('total_expenses')
                    ->label('Total Pengeluaran')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('danger')
                    ->size('sm')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('net_income')
                    ->label('Pendapatan Bersih')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('success')
                    ->size('sm')
                    ->weight('bold')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->size('sm'),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('Tanggal Dibuat')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })->columns(2),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Laporan laba rugi')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('calculate_net_income')
                        ->label('Hitung Laba Bersih')
                        ->icon('heroicon-o-calculator')
                        ->color('success')
                        ->action(function ($record) {
                            $record->net_income = $record->total_revenue - $record->total_expenses;
                            $record->save();

                            Notification::make()
                                ->title('Net income updated successfully')
                                ->icon('heroicon-o-check')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->tooltip('Rumus Laba Rugi: Laba Bersih = Pendapatan - Beban'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\Action::make('update_net_income')
                        ->label('Perbarui Laba Bersih')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->net_income = $record->total_revenue - $record->total_expenses;
                                $record->save();
                            });
                            Notification::make()
                                ->title('Laba bersih berhasil diperbarui')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->tooltip('Perbarui laba bersih untuk data yang dipilih')
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Perbarui Total Ekuitas')
                        ->modalDescription('Tindakan ini akan memperbarui total ekuitas untuk semua neraca yang dipilih berdasarkan total aset dan total kewajiban mereka.')
                        ->modalSubmitActionLabel('Perbarui')
                        ->color('warning'),
                    Tables\Actions\BulkAction::make('assignToFinancialReport')
                        ->label('Tetapkan ke Laporan Keuangan')
                        ->icon('heroicon-o-document-duplicate')
                        ->form([
                            Forms\Components\Select::make('financial_report_id')
                                ->label('Laporan Keuangan')
                                ->options(FinancialReport::pluck('report_name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function (IncomeStatement $record) use ($data) {
                                $record->update(['financial_report_id' => $data['financial_report_id']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('Tetapkan ke Laporan Keuangan')
                        ->modalDescription('Tindakan ini akan menetapkan laporan laba rugi yang dipilih ke laporan keuangan yang dipilih.')
                        ->modalSubmitActionLabel('Tetapkan')
                        ->color('primary'),
                ]),
            ]);
    }
}
