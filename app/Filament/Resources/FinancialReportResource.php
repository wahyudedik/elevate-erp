<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\ManagementFinancial\FinancialReport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\FinancialReportResource\Pages;
use App\Filament\Resources\FinancialReportResource\RelationManagers;

class FinancialReportResource extends Resource
{
    protected static ?string $model = FinancialReport::class;

    protected static ?string $navigationBadgeTooltip = 'Total Financial Reports';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Financial';

    protected static ?string $navigationParentItem = 'Financial Reporting';

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Financial Report')
                    ->schema([
                        Forms\Components\TextInput::make('report_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('report_type')
                            ->options([
                                'balance_sheet' => 'Balance Sheet',
                                'income_statement' => 'Income Statement',
                                'cash_flow' => 'Cash Flow',
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
                    ->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
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
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('report_name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_type')
                    ->badge()
                    ->colors([
                        'primary' => 'balance_sheet',
                        'success' => 'income_statement',
                        'danger' => 'cash_flow',
                    ])
                    ->icons([
                        'balance_sheet' => 'heroicon-o-scale',
                        'income_statement' => 'heroicon-o-currency-dollar',
                        'cash_flow' => 'heroicon-o-arrow-path',
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_period_start')
                    ->date()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_period_end')
                    ->date()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(string $state): string => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('report_type')
                    ->options([
                        'balance_sheet' => 'Balance Sheet',
                        'income_statement' => 'Income Statement',
                        'cash_flow' => 'Cash Flow',
                    ])
                    ->label('Report Type')
                    ->multiple()
                    ->preload(),

                Tables\Filters\Filter::make('report_period')
                    ->form([
                        Forms\Components\DatePicker::make('report_period_start')
                            ->label('Start Date'),
                        Forms\Components\DatePicker::make('report_period_end')
                            ->label('End Date'),
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
                    ->label('Has Notes')
                    ->placeholder('All Reports')
                    ->trueLabel('With Notes')
                    ->falseLabel('Without Notes')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('notes'),
                        false: fn(Builder $query) => $query->whereNull('notes'),
                    ),
            ])
            ->actions([
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-on-square-stack')
                    ->color('success')
                    ->action(function (FinancialReport $record) {
                        // Logic to generate and download PDF
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
                    }),
                Tables\Actions\Action::make('compare')
                    ->label('Compare Reports')
                    ->icon('heroicon-o-chart-bar')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('compare_with')
                            ->label('Compare with')
                            ->options(FinancialReport::pluck('report_name', 'id'))
                            ->required(),
                    ])
                    ->action(function (FinancialReport $record, array $data) {
                        // Logic to compare reports
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('exportSelected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            // Export logic here
                        }),
                    Tables\Actions\BulkAction::make('updateReportType')
                        ->label('Update Report Type')
                        ->icon('heroicon-o-document-text')
                        ->form([
                            Forms\Components\Select::make('report_type')
                                ->label('Report Type')
                                ->options([
                                    'balance_sheet' => 'Balance Sheet',
                                    'income_statement' => 'Income Statement',
                                    'cash_flow' => 'Cash Flow',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update(['report_type' => $data['report_type']]);
                        }),
                    Tables\Actions\BulkAction::make('updateReportPeriod')
                        ->label('Update Report Period')
                        ->icon('heroicon-o-calendar')
                        ->form([
                            Forms\Components\DatePicker::make('report_period_start')
                                ->label('Start Date')
                                ->required(),
                            Forms\Components\DatePicker::make('report_period_end')
                                ->label('End Date')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update([
                                'report_period_start' => $data['report_period_start'],
                                'report_period_end' => $data['report_period_end'],
                            ]);
                        }),
                    Tables\Actions\BulkAction::make('calculateTotals')
                        ->label('Calculate Totals')
                        ->icon('heroicon-o-calculator')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'total_assets' => $record->calculateTotalAssets(),
                                    'total_liabilities' => $record->calculateTotalLiabilities(),
                                    'net_income' => $record->calculateNetIncome(),
                                    'cash_flow' => $record->calculateCashFlow(),
                                ]);
                            });
                        }),
                ])
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Financial Report')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
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
}
