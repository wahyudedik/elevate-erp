<?php

namespace App\Filament\Resources\FinancialReportResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Jobs\ProcessCashFlow;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\CashFlowExporter;
use App\Models\ManagementFinancial\CashFlow;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementFinancial\FinancialReport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class CashFlowRelationManager extends RelationManager
{
    protected static string $relationship = 'cashFlow';

    protected static ?string $title = 'Arus Kas';

    protected static ?string $label = 'Arus Kas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Arus Kas')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->required()
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'Active'))
                            ->searchable()
                            ->label('Cabang')
                            ->preload(),
                        Forms\Components\TextInput::make('operating_cash_flow')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01)
                            ->label('Arus Kas Operasi'),
                        Forms\Components\TextInput::make('investing_cash_flow')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01)
                            ->label('Arus Kas Investasi'),
                        Forms\Components\TextInput::make('financing_cash_flow')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01)
                            ->label('Arus Kas Pendanaan'),
                        Forms\Components\TextInput::make('net_cash_flow')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01)
                            ->label('Arus Kas Bersih'),
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
            ->recordTitleAttribute('financial_report_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->size('sm')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->iconColor('primary')
                    ->sortable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('operating_cash_flow')
                    ->label('Arus Kas Operasi')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->color('success')
                    ->size('sm')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('investing_cash_flow')
                    ->label('Arus Kas Investasi')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('info')
                    ->size('sm')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('financing_cash_flow')
                    ->label('Arus Kas Pendanaan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('warning')
                    ->size('sm')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('net_cash_flow')
                    ->label('Arus Kas Bersih')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('primary')
                    ->size('sm')
                    ->weight('bold')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->size('sm')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->size('sm')
                    ->color('gray'),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('Pilih Rentang Tanggal')
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
                    ->label('Buat Laporan Arus Kas')
                    ->icon('heroicon-o-plus')
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('calculateTotals')
                        ->label('Calculate Totals')
                        ->icon('heroicon-o-calculator')
                        ->action(function (CashFlow $record) {
                            $record->net_cash_flow = $record->operating_cash_flow + $record->investing_cash_flow + $record->financing_cash_flow;
                            $record->save();

                            Notification::make()
                                ->title('Total Cash Flow Calculated')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Calculate Totals')
                        ->modalDescription('This action will calculate and update the net cash flow based on operating, investing, and financing cash flows.')
                        ->modalSubmitActionLabel('Calculate')
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->modalDescription('Are you sure you want to delete this balance sheet? This action cannot be undone.')
                        ->modalHeading('Delete Balance Sheet')
                        ->modalSubmitActionLabel('Delete')
                        ->color('danger'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('calculateTotals')
                        ->label('Hitung Total')
                        ->icon('heroicon-o-calculator')
                        ->action(function (Collection $records) {
                            $records->each(function (CashFlow $record) {
                                $record->net_cash_flow = $record->operating_cash_flow + $record->investing_cash_flow + $record->financing_cash_flow;
                                $record->save();
                            });

                            Notification::make()
                                ->title('Total Ekuitas Telah Dihitung')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->tooltip('Perbarui pendapatan bersih untuk catatan yang dipilih')
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
                            $records->each(function (CashFlow $record) use ($data) {
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
