<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Barryvdh\DomPDF\PDF;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Mail\SendEmailFinancialReportMail;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Clusters\FinancialReporting;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\FinancialReportExporter;
use App\Filament\Imports\FinancialReportImporter;
use App\Models\ManagementFinancial\FinancialReport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\FinancialReportResource\Pages;
use App\Filament\Resources\FinancialReportResource\RelationManagers;
use App\Filament\Resources\FinancialReportResource\RelationManagers\CashFlowRelationManager;
use App\Filament\Resources\FinancialReportResource\RelationManagers\BalanceSheetRelationManager;
use App\Filament\Resources\FinancialReportResource\RelationManagers\IncomeStatementRelationManager;

class FinancialReportResource extends Resource
{
    protected static ?string $model = FinancialReport::class;

    protected static ?string $navigationLabel = 'Laporan Keuangan';

    protected static ?string $modelLabel = 'Laporan Keuangan';

    protected static ?string $pluralModelLabel = 'Laporan Keuangan';

    protected static ?string $cluster = FinancialReporting::class;

    protected static ?int $navigationSort = 12;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'financialReport';

    protected static ?string $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Laporan Keuangan')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Cabang')
                            ->columnSpanFull(),
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
                            ->label('Jenis Laporan')
                            ->required(),
                        Forms\Components\DatePicker::make('report_period_start')
                            ->default(now())
                            ->required()
                            ->label('Periode Awal'),
                        Forms\Components\DatePicker::make('report_period_end')
                            ->required()
                            ->label('Periode Akhir'),
                        Forms\Components\RichEditor::make('notes')
                            ->columnSpanFull()
                            ->nullable()
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
                    ->icon('heroicon-m-building-storefront')
                    ->iconColor('primary')
                    ->sortable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('report_name')
                    ->label('Nama Laporan')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->size('sm')
                    ->weight('medium')
                    ->grow(false),
                Tables\Columns\TextColumn::make('report_type')
                    ->label('Jenis Laporan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'balance_sheet' => 'info',
                        'income_statement' => 'success',
                        'cash_flow' => 'danger',
                    })
                    ->icons([
                        'balance_sheet' => 'heroicon-o-scale',
                        'income_statement' => 'heroicon-o-currency-dollar',
                        'cash_flow' => 'heroicon-o-arrow-path',
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_period_start')
                    ->label('Periode Awal')
                    ->date('d M Y')
                    ->toggleable()
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('report_period_end')
                    ->label('Periode Akhir')
                    ->date('d M Y')
                    ->toggleable()
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
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                    ->searchable()
                    ->preload()
                    ->label('Cabang'),
                Tables\Filters\SelectFilter::make('report_type')
                    ->options([
                        'balance_sheet' => 'Neraca',
                        'income_statement' => 'Laporan Laba Rugi',
                        'cash_flow' => 'Arus Kas',
                    ])
                    ->label('Tipe Laporan')
                    ->multiple()
                    ->preload(),

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

                Tables\Filters\TernaryFilter::make('has_notes')
                    ->label('Memiliki Catatan')
                    ->placeholder('Semua Laporan')
                    ->trueLabel('Dengan Catatan')
                    ->falseLabel('Tanpa Catatan')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('notes'),
                        false: fn(Builder $query) => $query->whereNull('notes'),
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('generate_pdf')
                        ->label('Cetak PDF')
                        ->icon('heroicon-o-document')
                        ->color('warning')
                        ->action(function (FinancialReport $record) {
                            $pdf = null;

                            switch ($record->report_type) {
                                case 'balance_sheet':
                                    $balanceSheet = $record->balanceSheet;
                                    $pdf = app('dompdf.wrapper')->loadView('pdf.balance-sheet', [
                                        'report' => $record,
                                        'balanceSheet' => $balanceSheet
                                    ]);
                                    break;

                                case 'income_statement':
                                    $incomeStatement = $record->incomeStatement;
                                    $pdf = app('dompdf.wrapper')->loadView('pdf.income-statement', [
                                        'report' => $record,
                                        'incomeStatement' => $incomeStatement
                                    ]);
                                    break;

                                case 'cash_flow':
                                    $cashFlow = $record->cashFlow;
                                    $pdf = app('dompdf.wrapper')->loadView('pdf.cash-flow', [
                                        'report' => $record,
                                        'cashFlow' => $cashFlow
                                    ]);
                                    break;
                            }

                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, $record->report_name . '.pdf');
                        }),
                    Tables\Actions\Action::make('send_email')
                        ->label('Send Email')
                        ->icon('heroicon-o-envelope')
                        ->color('primary')
                        ->form([
                            Forms\Components\TextInput::make('email')
                                ->label('Recipient Email')
                                ->email()
                                ->required(),
                            Forms\Components\Textarea::make('message')
                                ->label('Message')
                                ->rows(3),
                        ])
                        ->action(function (FinancialReport $record, array $data) {
                            // Logic to send email
                            $financialReport = FinancialReport::find($record->id);
                            $financialReport->email = $data['email'];
                            $financialReport->message = $data['message'];

                            // dd($financialReport->balanceSheet, $financialReport->balanceSheet->toArray());

                            Mail::to($financialReport->email)->send(new SendEmailFinancialReportMail($financialReport));

                            Notification::make()
                                ->title('Email Sent' . ' ' . $data['email'])
                                ->body('Your email has been sent successfully.' . ' ' . $data['message'] . ' ' . $data['email'])
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Laporan Keuangan')
                    ->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()
                        ->label('Ekspor Laporan Keuangan')
                        ->exporter(FinancialReportExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Laporan Keuangan berhasil diekspor' . ' ' . date('Y-m-d'))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->label('Impor Laporan Keuangan')
                        ->importer(FinancialReportImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Laporan Keuangan berhasil diimpor' . ' ' . date('Y-m-d'))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                ])->icon('heroicon-o-cog-6-tooth')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateReportType')
                        ->label('Perbarui Tipe Laporan')
                        ->icon('heroicon-o-document-text')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('report_type')
                                ->label('Tipe Laporan')
                                ->options([
                                    'balance_sheet' => 'Neraca',
                                    'income_statement' => 'Laporan Laba Rugi',
                                    'cash_flow' => 'Arus Kas',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update(['report_type' => $data['report_type']]);
                        }),
                    Tables\Actions\BulkAction::make('updateReportPeriod')
                        ->label('Perbarui Periode Laporan')
                        ->icon('heroicon-o-calendar')
                        ->color('success')
                        ->form([
                            Forms\Components\DatePicker::make('report_period_start')
                                ->label('Tanggal Mulai')
                                ->required(),
                            Forms\Components\DatePicker::make('report_period_end')
                                ->label('Tanggal Selesai')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update([
                                'report_period_start' => $data['report_period_start'],
                                'report_period_end' => $data['report_period_end'],
                            ]);
                        }),
                    ExportBulkAction::make()
                        ->exporter(FinancialReportExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Laporan Keuangan berhasil diekspor' . ' ' . date('Y-m-d'))
                                ->success()
                                ->icon('heroicon-o-check')
                                ->sendToDatabase(Auth::user());
                        }),
                ])
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Laporan Keuangan')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BalanceSheetRelationManager::class,
            IncomeStatementRelationManager::class,
            CashFlowRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialReports::route('/'),
            'create' => Pages\CreateFinancialReport::route('/create'),
            'edit' => Pages\EditFinancialReport::route('/{record}/edit'),
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
            'report_name',
            'report_type', //'balance_sheet', 'income_statement', 'cash_flow'
            'report_period_start',
            'report_period_end',
            'notes',
        ];
    }
}
