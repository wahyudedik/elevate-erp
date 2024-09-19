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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cash Flow Details')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\TextInput::make('operating_cash_flow')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01),
                        Forms\Components\TextInput::make('investing_cash_flow')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01),
                        Forms\Components\TextInput::make('financing_cash_flow')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01),
                        Forms\Components\TextInput::make('net_cash_flow')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->minValue(-42949672.95)
                            ->step(0.01),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('financial_report_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('operating_cash_flow')
                    ->label('Operating Cash Flow')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('investing_cash_flow')
                    ->label('Investing Cash Flow')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('financing_cash_flow')
                    ->label('Financing Cash Flow')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('net_cash_flow')
                    ->label('Net Cash Flow')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('financial_report_id')
                    ->relationship('financialReport', 'report_name')
                    ->label('Financial Report')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\Filter::make('Select Date Range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
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
                        ->action(function (Collection $records) {
                            $records->each(function (CashFlow $record) {
                                $record->net_cash_flow = $record->operating_cash_flow + $record->investing_cash_flow + $record->financing_cash_flow;
                                $record->save();
                            });

                            Notification::make()
                                ->title('Total Equity Calculated')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Calculate Totals')
                        ->modalDescription('This action will calculate and update the total equity based on total assets and total liabilities.')
                        ->modalSubmitActionLabel('Calculate')
                        ->color('warning'),
                    Tables\Actions\Action::make('generateReport')
                        ->label('Generate Report')
                        ->icon('heroicon-o-document-text')
                        ->url(fn(CashFlow $record): string => route('Cash-flow.report', $record))
                        ->openUrlInNewTab()
                        ->color('success'),
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
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('calculateTotals')
                        ->label('Calculate Totals')
                        ->icon('heroicon-o-calculator')
                        ->action(function (Collection $records) {
                            $records->each(function (CashFlow $record) {
                                $record->net_cash_flow = $record->operating_cash_flow + $record->investing_cash_flow + $record->financing_cash_flow;
                                $record->save();
                            });

                            Notification::make()
                                ->title('Total Equity Calculated')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->tooltip('Update net income for selected records')
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Update Total Equity')
                        ->modalDescription('This action will update the total equity for all selected balance sheets based on their total assets and total liabilities.')
                        ->modalSubmitActionLabel('Update')
                        ->color('warning'),
                    Tables\Actions\BulkAction::make('assignToFinancialReport')
                        ->label('Assign to Financial Report')
                        ->icon('heroicon-o-document-duplicate')
                        ->form([
                            Forms\Components\Select::make('financial_report_id')
                                ->label('Financial Report')
                                ->options(FinancialReport::pluck('report_name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function (CashFlow $record) use ($data) {
                                $record->update(['financial_report_id' => $data['financial_report_id']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('Assign to Financial Report')
                        ->modalDescription('This action will assign the selected balance sheets to the chosen financial report.')
                        ->modalSubmitActionLabel('Assign')
                        ->color('primary'),
                    ExportBulkAction::make()
                        ->exporter(CashFlowExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Chas Flow exported successfully' . ' ' . date('Y-m-d'))
                                ->success()
                                ->icon('heroicon-o-check')
                                ->sendToDatabase(Auth::user());
                        }),
                ])
            ]);
    }
}
