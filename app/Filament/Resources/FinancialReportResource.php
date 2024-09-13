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

    protected static ?string $navigationParentItem = null;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
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
                            ->required(),
                        Forms\Components\DatePicker::make('report_period_end')
                            ->required(),
                        Forms\Components\TextInput::make('total_assets')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('total_liabilities')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('net_income')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('cash_flow')
                            ->numeric()
                            ->prefix('IDR'),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_type')
                    ->badge()
                    ->colors([
                        'primary' => 'balance_sheet',
                        'success' => 'income_statement',
                        'warning' => 'cash_flow',
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_period_start')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_period_end')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_assets')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_liabilities')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_income')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cash_flow')
                    ->money('IDR')
                    ->sortable(),
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
                    }),

                Tables\Filters\Filter::make('total_assets')
                    ->form([
                        Forms\Components\TextInput::make('min_total_assets')
                            ->label('Minimum Total Assets')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_total_assets')
                            ->label('Maximum Total Assets')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_total_assets'],
                                fn(Builder $query, $value): Builder => $query->where('total_assets', '>=', $value),
                            )
                            ->when(
                                $data['max_total_assets'],
                                fn(Builder $query, $value): Builder => $query->where('total_assets', '<=', $value),
                            );
                    }),

                Tables\Filters\Filter::make('total_liabilities')
                    ->form([
                        Forms\Components\TextInput::make('min_total_liabilities')
                            ->label('Minimum Total Liabilities')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_total_liabilities')
                            ->label('Maximum Total Liabilities')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_total_liabilities'],
                                fn(Builder $query, $value): Builder => $query->where('total_liabilities', '>=', $value),
                            )
                            ->when(
                                $data['max_total_liabilities'],
                                fn(Builder $query, $value): Builder => $query->where('total_liabilities', '<=', $value),
                            );
                    }),

                Tables\Filters\Filter::make('net_income')
                    ->form([
                        Forms\Components\TextInput::make('min_net_income')
                            ->label('Minimum Net Income')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_net_income')
                            ->label('Maximum Net Income')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_net_income'],
                                fn(Builder $query, $value): Builder => $query->where('net_income', '>=', $value),
                            )
                            ->when(
                                $data['max_net_income'],
                                fn(Builder $query, $value): Builder => $query->where('net_income', '<=', $value),
                            );
                    }),

                Tables\Filters\Filter::make('cash_flow')
                    ->form([
                        Forms\Components\TextInput::make('min_cash_flow')
                            ->label('Minimum Cash Flow')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_cash_flow')
                            ->label('Maximum Cash Flow')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_cash_flow'],
                                fn(Builder $query, $value): Builder => $query->where('cash_flow', '>=', $value),
                            )
                            ->when(
                                $data['max_cash_flow'],
                                fn(Builder $query, $value): Builder => $query->where('cash_flow', '<=', $value),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-download')
                    ->color('success')
                    ->action(function (FinancialReport $record) {
                        // Logic to generate and download PDF
                    }),
                Tables\Actions\Action::make('send_email')
                    ->label('Send Email')
                    ->icon('heroicon-o-mail')
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
                    ->label('Bulk Actions')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->color('primary'),
            ])
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Financial Report')
                    ->icon('heroicon-o-document-plus')
                    ->color('primary')
                    ->url(fn(): string => FinancialReportResource::getUrl('create')),
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
}
