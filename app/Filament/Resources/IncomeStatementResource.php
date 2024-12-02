<?php

namespace App\Filament\Resources;

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
use App\Filament\Exports\IncomeStatementExporter;
use App\Filament\Imports\IncomeStatementImporter;
use App\Models\ManagementFinancial\FinancialReport;
use App\Models\ManagementFinancial\IncomeStatement;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IncomeStatementResource\Pages;
use App\Filament\Resources\IncomeStatementResource\RelationManagers;
use App\Filament\Resources\IncomeStatementResource\RelationManagers\FinancialReportRelationManager;
use Filament\Tables\Actions\CreateAction;

class IncomeStatementResource extends Resource
{
    protected static ?string $model = IncomeStatement::class;

    protected static ?string $navigationLabel = 'Laporan Laba Rugi';

    protected static ?string $modelLabel = 'Laporan Laba Rugi';

    protected static ?string $pluralModelLabel = 'Laporan Laba Rugi';

    protected static ?string $cluster = FinancialReporting::class;

    protected static ?int $navigationSort = 15;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'incomeStatement';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-long-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Laporan Laba Rugi')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->searchable()
                            ->preload()
                            ->label('Cabang')
                            ->placeholder('Pilih Cabang')
                            ->nullable()
                            ->columnSpan(2),
                        Forms\Components\Select::make('financial_report_id')
                            ->relationship('financialReport', 'report_name', fn(Builder $query) => $query->where('report_type', 'income_statement'))
                            ->searchable()
                            ->preload()
                            ->label('Laporan Keuangan')
                            ->placeholder('Pilih Laporan Keuangan')
                            ->nullable()
                            ->createOptionForm([
                                Forms\Components\Hidden::make('company_id')
                                    ->default(Filament::getTenant()->id),
                                Forms\Components\Select::make('branch_id')
                                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                                    ->searchable()
                                    ->preload()
                                    ->label('Cabang')
                                    ->placeholder('Pilih Cabang')
                                    ->required(),
                                Forms\Components\TextInput::make('report_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('report_type')
                                    ->options([
                                        'balance_sheet' => 'Neraca',
                                        'income_statement' => 'Laporan Laba Rugi',
                                        'cash_flow' => 'Arus Kas',
                                    ])
                                    ->required(),
                                Forms\Components\DatePicker::make('report_period_start')
                                    ->default(now())
                                    ->required(),
                                Forms\Components\DatePicker::make('report_period_end')
                                    ->required(),
                                Forms\Components\Textarea::make('notes')
                                    ->nullable(),
                            ])
                            ->columnSpan(2),
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
                            ->label('Laba Bersih'),
                    ])
                    ->columns(2)
                    ->collapsible(),
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
                    ->size('sm')
                    ->badge(),
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
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Pendapatan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->size('sm')
                    ->weight('bold')
                    ->color('success'),
                Tables\Columns\TextColumn::make('total_expenses')
                    ->label('Total Pengeluaran')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->size('sm')
                    ->weight('bold')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('net_income')
                    ->label('Laba Bersih')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->size('sm')
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->size('sm')
                    ->icon('heroicon-o-clock'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->size('sm')
                    ->icon('heroicon-o-arrow-path'),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                    ->label('Cabang')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('financial_report')
                    ->relationship('financialReport', 'report_name', fn($query) => $query->where('report_type', 'income_statement'))
                    ->label('Laporan Keuangan')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('Date Picked')
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
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('calculate_net_income')
                        ->label('Hitung Total')
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
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus')->label('Buat Laporan Laba Rugi'),
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(IncomeStatementExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->label('Ekspor')
                        ->after(function () {
                            Notification::make()
                                ->title('Laporan Laba Rugi berhasil diekspor' . ' ' . now()->format('d-m-Y H:i:s'))
                                ->success()
                                ->icon('heroicon-o-check-circle')
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(IncomeStatementImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->label('Impor')
                        ->after(function () {
                            Notification::make()
                                ->title('Laporan Laba Rugi berhasil diimpor' .  ' ' . now()->format('d-m-Y H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                ])->icon('heroicon-o-cog-6-tooth'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('update_net_income')
                        ->label('Perbarui Laba Bersih')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'net_income' => $record->total_revenue - $record->total_expenses
                                ]);
                            }
                            Notification::make()
                                ->title('Laba bersih berhasil diperbarui')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->tooltip('Perbarui laba bersih untuk data yang dipilih')
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Perbarui Laba Bersih')
                        ->modalDescription('Tindakan ini akan memperbarui laba bersih untuk semua laporan laba rugi yang dipilih berdasarkan total pendapatan dan total beban.')
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
                    ExportBulkAction::make()
                        ->exporter(IncomeStatementExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Laporan Laba Rugi berhasil diekspor' .  ' ' . now()->format('d-m-Y H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Laporan Laba Rugi')
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
            'index' => Pages\ListIncomeStatements::route('/'),
            'create' => Pages\CreateIncomeStatement::route('/create'),
            'edit' => Pages\EditIncomeStatement::route('/{record}/edit'),
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
            'total_revenue',
            'total_expenses',
            'net_income',
        ];
    }
}
