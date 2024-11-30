<?php

namespace App\Filament\Resources\FinancialReportResource\RelationManagers;

use Carbon\Carbon;
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
use App\Filament\Exports\BalanceSheetExporter;
use App\Models\ManagementFinancial\BalanceSheet;
use App\Models\ManagementFinancial\FinancialReport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class BalanceSheetRelationManager extends RelationManager
{
    protected static string $relationship = 'balanceSheet';

    protected static ?string $title = 'Neraca';

    protected static ?string $label = 'Neraca';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Balance Sheet Details')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->required()
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'Active'))
                            ->searchable()
                            ->preload()
                            ->label('Cabang'),
                        Forms\Components\TextInput::make('total_assets')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->default(0)
                            ->label('Total Aset'),
                        Forms\Components\TextInput::make('total_liabilities')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->default(0)
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->label('Total Kewajiban'),
                        Forms\Components\TextInput::make('total_equity')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->default(0)
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->label('Total Ekuitas'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Additional Information')
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
                Tables\Columns\TextColumn::make('total_assets')
                    ->label('Total Aset')
                    ->money('IDR')
                    ->color('success')
                    ->size('sm')
                    ->weight('bold')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_liabilities')
                    ->label('Total Kewajiban')
                    ->money('IDR')
                    ->color('danger')
                    ->size('sm')
                    ->weight('bold')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_equity')
                    ->label('Total Ekuitas')
                    ->money('IDR')
                    ->color('warning')
                    ->size('sm')
                    ->weight('bold')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime()
                    ->sortable()
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Dibuat dari ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Dibuat sampai ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columnSpan(2),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Laporan Neraca')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->modalDescription('Apakah anda yakin ingin menghapus laporan neraca ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalHeading('Hapus Laporan Neraca')
                        ->modalSubmitActionLabel('Hapus')
                        ->color('danger'),
                    Tables\Actions\Action::make('calculateTotals')
                        ->label('Hitung Total')
                        ->icon('heroicon-o-calculator')
                        ->action(function (BalanceSheet $record) {
                            $record->total_equity = $record->total_assets - $record->total_liabilities;
                            $record->save();

                            Notification::make()
                                ->title('Total Ekuitas Telah Dihitung')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Hitung Total')
                        ->modalDescription('Tindakan ini akan menghitung dan memperbarui total ekuitas berdasarkan total aset dan total kewajiban.')
                        ->modalSubmitActionLabel('Hitung')
                        ->color('warning'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateTotalEquity')
                        ->label('Perbarui Total Ekuitas')
                        ->icon('heroicon-o-calculator')
                        ->action(function (Collection $records) {
                            $records->each(function (BalanceSheet $record) {
                                $record->total_equity = $record->total_assets - $record->total_liabilities;
                                $record->save();
                            });
                        })
                        ->tooltip('Perbarui total ekuitas untuk data yang dipilih')
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
                            $records->each(function (BalanceSheet $record) use ($data) {
                                $record->update(['financial_report_id' => $data['financial_report_id']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('Tetapkan ke Laporan Keuangan')
                        ->modalDescription('Tindakan ini akan menetapkan neraca yang dipilih ke laporan keuangan yang dipilih.')
                        ->modalSubmitActionLabel('Tetapkan')
                        ->color('primary'),
                ])
            ]);
    }
}
