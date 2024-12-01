<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Clusters\FinancialReporting;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\BalanceSheetExporter;
use App\Filament\Imports\BalanceSheetImporter;
use App\Models\ManagementFinancial\BalanceSheet;
use App\Models\ManagementFinancial\FinancialReport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BalanceSheetResource\Pages;
use App\Filament\Resources\BalanceSheetResource\RelationManagers;
use App\Filament\Resources\BalanceSheetResource\RelationManagers\FinancialReportRelationManager;
use Filament\Tables\Actions\CreateAction;

class BalanceSheetResource extends Resource
{
    protected static ?string $model = BalanceSheet::class;

    protected static ?string $navigationLabel = 'Laporan Neraca';

    protected static ?string $modelLabel = 'Laporan Neraca';

    protected static ?string $pluralModelLabel = 'Laporan Neraca';

    protected static ?string $cluster = FinancialReporting::class;

    protected static ?int $navigationSort = 13;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'balanceSheet';

    protected static ?string $navigationGroup = 'Financial Reporting';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-long-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Laporan Neraca')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Cabang'),
                        Forms\Components\Select::make('financial_report_id')
                            ->relationship('financialReport', 'report_name', fn(Builder $query) => $query->where('report_type', 'balance_sheet'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->createOptionForm([
                                Forms\Components\Hidden::make('company_id')
                                    ->default(Filament::getTenant()->id),
                                Forms\Components\Select::make('branch_id')
                                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label('Cabang'),
                                Forms\Components\TextInput::make('report_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Nama Laporan'),
                                Forms\Components\Select::make('report_type')
                                    ->options([
                                        'balance_sheet' => 'Laporan Neraca',
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
                                    ->nullable()
                                    ->label('Catatan'),
                            ])->columns(2)
                            ->label('Laporan Keuangan'),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->weight('lg'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary')
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('financialReport.report_name')
                    ->label('Laporan Keuangan')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-document-text')
                    ->iconColor('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_assets')
                    ->label('Total Aset')
                    ->money('IDR')
                    ->toggleable()
                    ->color('success')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_liabilities')
                    ->label('Total Kewajiban')
                    ->money('IDR')
                    ->toggleable()
                    ->color('danger')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_equity')
                    ->label('Total Ekuitas')
                    ->money('IDR')
                    ->toggleable()
                    ->color('warning')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-arrow-path')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                    ->searchable()
                    ->preload()
                    ->label('Cabang'),
                Tables\Filters\SelectFilter::make('financial_report')
                    ->relationship('financialReport', 'report_name', fn($query) => $query->where('report_type', 'balance_sheet'))
                    ->searchable()
                    ->preload()
                    ->label('Laporan Keuangan'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dibuat Dari'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Dibuat Sampai'),
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
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('calculateTotals')
                        ->label('Hitung Total')
                        ->icon('heroicon-o-calculator')
                        ->action(function (BalanceSheet $record) {
                            $record->total_equity = $record->total_assets - $record->total_liabilities;
                            $record->save();

                            Notification::make()
                                ->title('Total Ekuitas Terhitung')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Hitung Total')
                        ->modalDescription('Tindakan ini akan menghitung dan memperbarui total ekuitas berdasarkan total aset dan total kewajiban.')
                        ->modalSubmitActionLabel('Hitung')
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->modalDescription('Apakah Anda yakin ingin menghapus neraca ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalHeading('Hapus Neraca')
                        ->modalSubmitActionLabel('Hapus')
                        ->color('danger'),
                ])
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Laporan Neraca')
                    ->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(BalanceSheetExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Neraca berhasil diekspor' . ' ' . date('Y-m-d'))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(BalanceSheetImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Neraca berhasil diimpor' . ' ' . date('Y-m-d'))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                ])->icon('heroicon-o-cog-6-tooth')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateTotalEquity')
                        ->label('Perbarui Total Ekuitas')
                        ->icon('heroicon-o-calculator')
                        ->action(function (Collection $records) {
                            $records->each(function (BalanceSheet $record) {
                                $record->total_equity = $record->total_assets - $record->total_liabilities;
                                $record->save();
                            });
                        })
                        ->tooltip('Perbarui total ekuitas untuk data terpilih')
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
                    ExportBulkAction::make()
                        ->exporter(BalanceSheetExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Neraca berhasil diekspor' . ' ' . date('Y-m-d'))
                                ->success()
                                ->icon('heroicon-o-check')
                                ->sendToDatabase(Auth::user());
                        }),
                ])
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Laporan Neraca')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            FinancialReportRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBalanceSheets::route('/'),
            'create' => Pages\CreateBalanceSheet::route('/create'),
            'edit' => Pages\EditBalanceSheet::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'branch_id',
            'company_id',
            'financial_report_id',
            'total_assets',
            'total_liabilities',
            'total_equity',
        ];
    }
}
