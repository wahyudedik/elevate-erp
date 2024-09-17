<?php

namespace App\Filament\Resources\FinancialReportResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Balance Sheet Details')
                    ->schema([
                        Forms\Components\TextInput::make('total_assets')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->default(0)
                            ->label('Total Assets'),
                        Forms\Components\TextInput::make('total_liabilities')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->default(0)
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->label('Total Liabilities'),
                        Forms\Components\TextInput::make('total_equity')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->default(0)
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->label('Total Equity'),
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
                Tables\Columns\TextColumn::make('total_assets')
                    ->label('Total Assets')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_liabilities')
                    ->label('Total Liabilities')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_equity')
                    ->label('Total Equity')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('financial_report')
                    ->relationship('financialReport', 'report_name')
                    ->searchable()
                    ->preload()
                    ->label('Financial Report'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
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
                            $indicators['created_from'] = 'Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columnSpan(2),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->icon('heroicon-o-plus'),
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
                        ->action(function (BalanceSheet $record) {
                            $record->total_equity = $record->total_assets - $record->total_liabilities;
                            $record->save();

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
                        ->url(fn(BalanceSheet $record): string => route('balance-sheet.report', $record))
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
                    Tables\Actions\BulkAction::make('updateTotalEquity')
                        ->label('Update Total Equity')
                        ->icon('heroicon-o-calculator')
                        ->action(function (Collection $records) {
                            $records->each(function (BalanceSheet $record) {
                                $record->total_equity = $record->total_assets - $record->total_liabilities;
                                $record->save();
                            });
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
                            $records->each(function (BalanceSheet $record) use ($data) {
                                $record->update(['financial_report_id' => $data['financial_report_id']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('Assign to Financial Report')
                        ->modalDescription('This action will assign the selected balance sheets to the chosen financial report.')
                        ->modalSubmitActionLabel('Assign')
                        ->color('primary'),
                    ExportBulkAction::make()
                        ->exporter(BalanceSheetExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Balance Sheet exported successfully' . ' ' . date('Y-m-d'))
                                ->success()
                                ->icon('heroicon-o-check')
                                ->sendToDatabase(Auth::user());
                        }),
                ])
            ]);
    }
}