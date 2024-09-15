<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\IncomeStatementExporter;
use App\Filament\Imports\IncomeStatementImporter;
use App\Models\ManagementFinancial\FinancialReport;
use App\Models\ManagementFinancial\IncomeStatement;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IncomeStatementResource\Pages;
use App\Filament\Resources\IncomeStatementResource\RelationManagers;
use App\Filament\Resources\IncomeStatementResource\RelationManagers\FinancialReportRelationManager;

class IncomeStatementResource extends Resource
{
    protected static ?string $model = IncomeStatement::class;

    protected static ?string $navigationBadgeTooltip = 'Total Income Statements';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Financial';

    protected static ?string $navigationParentItem = 'Financial Reporting';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Income Statement Details')
                    ->schema([
                        Forms\Components\Select::make('financial_report_id')
                            ->relationship('financialReport', 'report_name', fn(Builder $query) => $query->where('report_type', 'income_statement'))
                            ->searchable()
                            ->preload()
                            ->label('Financial Report')
                            ->placeholder('Select a Financial Report')
                            ->nullable()
                            ->createOptionForm([
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
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('total_revenue')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->label('Total Revenue'),
                        Forms\Components\TextInput::make('total_expenses')
                            ->required()
                            ->default(0)
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->label('Total Expenses'),
                        Forms\Components\TextInput::make('net_income')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->step(0.01)
                            ->label('Net Income'),
                    ])
                    ->columns(2)
                    ->collapsible(),
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
                Tables\Columns\TextColumn::make('financialReport.report_name')
                    ->label('Financial Report')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_expenses')
                    ->label('Total Expenses')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('net_income')
                    ->label('Net Income')
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
                Tables\Filters\SelectFilter::make('financial_report')
                    ->relationship('financialReport', 'report_name')
                    ->label('Financial Report')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('total_revenue')
                    ->form([
                        Forms\Components\TextInput::make('total_revenue_from')
                            ->label('Revenue From')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('total_revenue_to')
                            ->label('Revenue To')
                            ->numeric()
                            ->prefix('IDR'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['total_revenue_from'],
                                fn(Builder $query, $value): Builder => $query->where('total_revenue', '>=', $value),
                            )
                            ->when(
                                $data['total_revenue_to'],
                                fn(Builder $query, $value): Builder => $query->where('total_revenue', '<=', $value),
                            );
                    })->columns(2),

                Tables\Filters\Filter::make('total_expenses')
                    ->form([
                        Forms\Components\TextInput::make('total_expenses_from')
                            ->label('Expenses From')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('total_expenses_to')
                            ->label('Expenses To')
                            ->numeric()
                            ->prefix('IDR'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['total_expenses_from'],
                                fn(Builder $query, $value): Builder => $query->where('total_expenses', '>=', $value),
                            )
                            ->when(
                                $data['total_expenses_to'],
                                fn(Builder $query, $value): Builder => $query->where('total_expenses', '<=', $value),
                            );
                    })->columns(2),

                Tables\Filters\Filter::make('net_income')
                    ->form([
                        Forms\Components\TextInput::make('net_income_from')
                            ->label('Net Income From')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('net_income_to')
                            ->label('Net Income To')
                            ->numeric()
                            ->prefix('IDR'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['net_income_from'],
                                fn(Builder $query, $value): Builder => $query->where('net_income', '>=', $value),
                            )
                            ->when(
                                $data['net_income_to'],
                                fn(Builder $query, $value): Builder => $query->where('net_income', '<=', $value),
                            );
                    })->columns(2),

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
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('generate_report')
                        ->label('Generate Report')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn(IncomeStatement $record): string => route('income-statement.report', $record))
                        ->openUrlInNewTab()
                        ->tooltip('Generate a detailed income statement report'),
                    Tables\Actions\Action::make('calculate_net_income')
                        ->label('Calculate Totals')
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
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(IncomeStatementExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Income Statment exported successfully' . ' ' . now()->format('d-m-Y H:i:s'))
                                ->success()
                                ->icon('heroicon-o-check-circle')
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(IncomeStatementImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('warning')
                        ->after(function () {
                            Notification::make()
                                ->title('Income Statment imported successfully' .  ' ' . now()->format('d-m-Y H:i:s'))
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
                    Tables\Actions\Action::make('update_net_income')
                        ->label('Update Net Income')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->net_income = $record->total_revenue - $record->total_expenses;
                                $record->save();
                            });
                            Notification::make()
                                ->title('Net income updated successfully')
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
                            $records->each(function (IncomeStatement $record) use ($data) {
                                $record->update(['financial_report_id' => $data['financial_report_id']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('Assign to Financial Report')
                        ->modalDescription('This action will assign the selected income statements to the chosen financial report.')
                        ->modalSubmitActionLabel('Assign')
                        ->color('primary'),
                    ExportBulkAction::make()
                        ->exporter(IncomeStatementExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Income Statment exported successfully' .  ' ' . now()->format('d-m-Y H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Income Statement')
                    ->color('success')
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
}
