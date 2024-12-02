<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Jobs\ProcessCashFlow;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\CashFlowExporter;
use App\Filament\Imports\CashFlowImporter;
use App\Models\ManagementFinancial\CashFlow;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Clusters\FinancialReporting;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\CashFlowResource\Pages;
use App\Models\ManagementFinancial\FinancialReport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CashFlowResource\RelationManagers;
use App\Filament\Resources\CashFlowResource\RelationManagers\FinancialReportRelationManager;

class CashFlowResource extends Resource
{
    protected static ?string $model = CashFlow::class;

    protected static ?string $navigationLabel = 'Laporan Arus Kas';

    protected static ?string $modelLabel = 'Laporan Arus Kas';

    protected static ?string $pluralModelLabel = 'Laporan Arus Kas';

    protected static ?string $cluster = FinancialReporting::class;

    protected static ?int $navigationSort = 14;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'cashFlow';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-long-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Arus Kas')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->label('Cabang')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('financial_report_id')
                            ->relationship('financialReport', 'report_name', fn(Builder $query) => $query->where('report_type', 'cash_flow'))
                            ->label('Laporan Keuangan')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                // Add fields for creating a new financial report if needed
                                Forms\Components\Hidden::make('company_id')
                                    ->default(Filament::getTenant()->id),
                                Forms\Components\Select::make('branch_id')
                                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                                    ->label('Cabang')
                                    ->searchable()
                                    ->preload()
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
                                    ->nullable(),
                            ])
                            ->nullable(),
                        Forms\Components\TextInput::make('operating_cash_flow')
                            ->label('Arus Kas Operasi')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01),
                        Forms\Components\TextInput::make('investing_cash_flow')
                            ->label('Arus Kas Investasi')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01),
                        Forms\Components\TextInput::make('financing_cash_flow')
                            ->label('Arus Kas Pendanaan')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01),
                        Forms\Components\TextInput::make('net_cash_flow')
                            ->label('Arus Kas Bersih')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01),
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
                Tables\Columns\TextColumn::make('financialReport.report_name')
                    ->label('Laporan Keuangan')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->size('sm')
                    ->weight('medium')
                    ->icon('heroicon-o-document-text')
                    ->iconColor('success'),
                Tables\Columns\TextColumn::make('operating_cash_flow')
                    ->label('Arus Kas Operasi')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->color('primary')
                    ->size('sm')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('investing_cash_flow')
                    ->label('Arus Kas Investasi')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('warning')
                    ->size('sm')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('financing_cash_flow')
                    ->label('Arus Kas Pendanaan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('success')
                    ->size('sm')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('net_cash_flow')
                    ->label('Arus Kas Bersih')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('danger')
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
                    ->label('Terakhir Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->size('sm')
                    ->color('gray'),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                    ->label('Cabang')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('financial_report_id')
                    ->relationship('financialReport', 'report_name', fn($query) => $query->where('report_type', 'cash_flow'))
                    ->label('Laporan Keuangan')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\Filter::make('Select Date Range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai'),
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
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('calculateTotals')
                        ->label('Hitung Total')
                        ->icon('heroicon-o-calculator')
                        ->action(function (CashFlow $record) {
                            $operating = $record->operating_cash_flow ? floatval($record->operating_cash_flow) : 0;
                            $investing = $record->investing_cash_flow ? floatval($record->investing_cash_flow) : 0;
                            $financing = $record->financing_cash_flow ? floatval($record->financing_cash_flow) : 0;

                            $record->net_cash_flow = $operating + $investing + $financing;
                            $record->save();

                            Notification::make()
                                ->title('Arus Kas Telah Dihitung')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Hitung Total')
                        ->modalDescription('Tindakan ini akan menghitung dan memperbarui total arus kas berdasarkan arus kas operasi, investasi, dan pendanaan.')
                        ->modalSubmitActionLabel('Hitung')
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->modalDescription('Apakah Anda yakin ingin menghapus laporan arus kas ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalHeading('Hapus Laporan Arus Kas')
                        ->modalSubmitActionLabel('Hapus')
                        ->color('danger'),
                ])
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Laporan Arus Kas')
                    ->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(CashFlowExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Arus Kas berhasil diekspor' . ' ' . date('Y-m-d'))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(CashFlowImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Arus Kas berhasil diimpor' . ' ' . date('Y-m-d'))
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
                    Tables\Actions\BulkAction::make('calculateTotals')
                        ->label('Hitung Total')
                        ->icon('heroicon-o-calculator')
                        ->action(function (Collection $records) {
                            $records->each(function (CashFlow $record) {
                                $record->net_cash_flow = $record->operating_cash_flow + $record->investing_cash_flow + $record->financing_cash_flow;
                                $record->save();
                            });

                            Notification::make()
                                ->title('Total Arus Kas Telah Dihitung')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->tooltip('Perbarui arus kas bersih untuk catatan yang dipilih')
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Perbarui Total Arus Kas')
                        ->modalDescription('Tindakan ini akan memperbarui total arus kas untuk semua laporan yang dipilih berdasarkan arus kas operasi, investasi, dan pendanaan.')
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
                        ->modalDescription('Tindakan ini akan menetapkan laporan yang dipilih ke laporan keuangan yang dipilih.')
                        ->modalSubmitActionLabel('Tetapkan')
                        ->color('primary'),
                    ExportBulkAction::make()
                        ->exporter(CashFlowExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Arus Kas berhasil diekspor' . ' ' . date('Y-m-d'))
                                ->success()
                                ->icon('heroicon-o-check')
                                ->sendToDatabase(Auth::user());
                        }),
                ])
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Buat Laporan Arus Kas')
                    ->icon('heroicon-o-plus')
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
            'index' => Pages\ListCashFlows::route('/'),
            'create' => Pages\CreateCashFlow::route('/create'),
            'edit' => Pages\EditCashFlow::route('/{record}/edit'),
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
            'company_id',
            'branch_id',
            'financial_report_id',
            'operating_cash_flow',
            'investing_cash_flow',
            'financing_cash_flow',
            'net_cash_flow',
        ];
    }
}
