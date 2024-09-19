<?php

namespace App\Filament\Resources\AccountingResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementFinancial\Ledger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class LedgerRelationManager extends RelationManager
{
    protected static string $relationship = 'ledger';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ledger')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->required()
                            ->format('Y-m-d')
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->columnSpanFull()
                            ->native(false),
                        Forms\Components\Select::make('transaction_type')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Credit',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->mask('999999999999999.99')
                            ->prefix('IDR')
                            ->required(),
                        Forms\Components\Textarea::make('transaction_description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created At')
                            ->default('-')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last Modified At')
                            ->default('-')
                            ->content(fn($record): string => $record?->updated_at ? $record->updated_at->diffForHumans() : '-'),
                    ])->columns(2)
                    ->collapsible()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('account_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->label('Type')
                    ->icon(fn(string $state): string => match ($state) {
                        'credit' => 'heroicon-o-arrow-up-circle',
                        'debit' => 'heroicon-o-arrow-down-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->colors([
                        'success' => 'debit',
                        'danger' => 'credit',
                    ])
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_description')
                    ->label('Description')
                    ->limit(50)
                    ->wrap()
                    ->searchable()
                    ->toggleable()
                    ->tooltip(fn($record) => $record->transaction_description),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('account')
                    ->relationship('account', 'account_name')
                    ->searchable()
                    ->preload()
                    ->label('Account'),
                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From ' . Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Until ' . Carbon::parse($data['until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                    ])
                    ->label('Transaction Type'),
                Tables\Filters\Filter::make('amount')
                    ->form([
                        Forms\Components\TextInput::make('min')
                            ->label('Minimum Amount')
                            ->numeric(),
                        Forms\Components\TextInput::make('max')
                            ->label('Maximum Amount')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $min): Builder => $query->where('amount', '>=', $min),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $max): Builder => $query->where('amount', '<=', $max),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['min'] ?? null) {
                            $indicators['min'] = 'Min: $' . number_format($data['min'], 2);
                        }
                        if ($data['max'] ?? null) {
                            $indicators['max'] = 'Max: $' . number_format($data['max'], 2);
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('transaction_description')
                    ->label('Has Description')
                    ->nullable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('print')
                        ->label('Print')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn(Ledger $record): string => route('ledger.print', $record))
                        ->openUrlInNewTab(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateTransactionType')
                        ->label('Update Transaction Type')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Select::make('transaction_type')
                                ->label('Transaction Type')
                                ->options([
                                    'debit' => 'Debit',
                                    'credit' => 'Credit',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'transaction_type' => $data['transaction_type'],
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('updateTransactionDate')
                        ->label('Update Transaction Date')
                        ->icon('heroicon-o-calendar')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\DatePicker::make('transaction_date')
                                ->label('Transaction Date')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'transaction_date' => $data['transaction_date'],
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ])->label('Bulk Actions'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
