<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Exports\LedgerExporter;
use App\Filament\Imports\LedgerImporter;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementFinancial\Ledger;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementFinancial\Transaction;
use App\Filament\Resources\LedgerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LedgerResource\RelationManagers;
use App\Filament\Resources\LedgerResource\RelationManagers\AccountRelationManager;
use App\Filament\Resources\LedgerResource\RelationManagers\TransactionsRelationManager;

class LedgerResource extends Resource
{
    protected static ?string $model = Ledger::class;

    protected static ?string $navigationBadgeTooltip = 'Total Ledgers';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'ledger';

    protected static ?string $navigationGroup = 'Management Financial';

    protected static ?string $navigationParentItem = 'Book Keeping';

    protected static ?string $navigationIcon = 'tabler-report-analytics';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ledger Details')
                    ->schema([
                        Forms\Components\Select::make('account_id')
                            ->relationship('account', 'account_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Account'),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->required()
                            ->default(now())
                            ->label('Transaction Date'),
                        Forms\Components\Select::make('transaction_type')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Credit',
                            ])
                            ->required()
                            ->label('Transaction Type'),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->default(0)
                            ->rule('decimal:0,2')
                            ->label('Amount'),
                        Forms\Components\Textarea::make('transaction_description')
                            ->nullable()
                            ->columnSpanFull()
                            ->label('Transaction Description'),
                    ])->columns(2),
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
                Tables\Columns\TextColumn::make('account.account_name')
                    ->label('Account')
                    ->sortable()
                    ->toggleable()
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Transaction Date')
                    ->date()
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->toggleable()
                    ->label('Transaction Type')
                    ->icon(fn(string $state): string => match ($state) {
                        'credit' => 'heroicon-o-arrow-up-circle',
                        'debit' => 'heroicon-o-arrow-down-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->colors([
                        'success' => 'debit',
                        'danger' => 'credit',
                    ])
                    ->sortable()
                    ->searchable(),
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
                    ->toggleable()
                    ->searchable(),
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
            ->defaultSort('transaction_date', 'desc')
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
            ->headerActions([
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(LedgerExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ledger exported successfully' . ' ' . now()->toDateTimeString())
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(LedgerImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('warning')
                        ->after(function () {
                            Notification::make()
                                ->title('Ledger imported successfully' . ' ' . now()->toDateTimeString())
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                ])->icon('heroicon-o-cog-6-tooth'),
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
                    ExportBulkAction::make()
                        ->exporter(LedgerExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ledger exported successfully' . ' ' . now()->format('Y-m-d H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->label('Bulk Actions'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AccountRelationManager::class,
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLedgers::route('/'),
            'create' => Pages\CreateLedger::route('/create'),
            'edit' => Pages\EditLedger::route('/{record}/edit'),
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
