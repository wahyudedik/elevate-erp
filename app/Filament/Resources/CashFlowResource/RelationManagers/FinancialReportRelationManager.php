<?php

namespace App\Filament\Resources\CashFlowResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class FinancialReportRelationManager extends RelationManager
{
    protected static string $relationship = 'financialReport';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Financial Report')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->required()
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload(),
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
                            ->nullable()->columnSpan(2),
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
                Tables\Columns\TextColumn::make('branch.name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->toggleable(),
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
                Tables\Filters\SelectFilter::make('report_type')
                    ->options([
                        'balance_sheet' => 'Balance Sheet',
                        'income_statement' => 'Income Statement',
                        'cash_flow' => 'Cash Flow',
                    ])
                    ->label('Report Type')
                    ->multiple(),
                Tables\Filters\filter::make('report_period')
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
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->label('Branch')
                    ->multiple()
                    ->preload()
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
