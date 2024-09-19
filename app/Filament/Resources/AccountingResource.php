<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use OpenSpout\Writer\CSV\Writer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementFinancial\Ledger;
use App\Filament\Exports\AccountingExporter;
use App\Filament\Imports\AccountingImporter;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementFinancial\Accounting;
use App\Models\ManagementFinancial\Transaction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AccountingResource\Pages;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Filament\Resources\AccountingResource\RelationManagers;
use App\Filament\Resources\AccountingResource\RelationManagers\LedgerRelationManager;
use App\Filament\Resources\AccountingResource\RelationManagers\JournalEntriesRelationManager;

class AccountingResource extends Resource
{
    protected static ?string $model = Accounting::class;

    protected static ?string $navigationBadgeTooltip = 'Total Accounts';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }


    protected static ?string $tenantRelationshipName = 'accounting';

    protected static ?string $navigationGroup = 'Management Financial';

    protected static ?string $navigationParentItem = 'Accounts';

    protected static ?string $navigationIcon = 'mdi-finance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('account_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_number')
                            ->required()
                            ->unique(ignorable: fn($record) => $record)
                            ->maxLength(255),
                        Forms\Components\Select::make('account_type')
                            ->required()
                            ->options([
                                'asset' => 'Asset',
                                'liability' => 'Liability',
                                'equity' => 'Equity',
                                'revenue' => 'Revenue',
                                'expense' => 'Expense',
                            ]),
                        Forms\Components\TextInput::make('initial_balance')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->default(0)
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('current_balance', $state);
                            }),
                        Forms\Components\TextInput::make('current_balance')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('initial_balance', $state);
                            }),
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
                Tables\Columns\TextColumn::make('account_name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->icon('heroicon-o-hashtag'),
                Tables\Columns\TextColumn::make('account_type')
                    ->icon('heroicon-o-currency-dollar')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'primary' => 'asset',
                        'danger' => 'liability',
                        'warning' => 'equity',
                        'success' => 'revenue',
                        'info' => 'expense',
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('initial_balance')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->money('IDR')
                    ->toggleable()
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
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('account_type')
                    ->options([
                        'asset' => 'Asset',
                        'liability' => 'Liability',
                        'equity' => 'Equity',
                        'revenue' => 'Revenue',
                        'expense' => 'Expense',
                    ])
                    ->label('Account Type')
                    ->multiple()
                    ->preload(),
                Tables\Filters\Filter::make('high_balance')
                    ->form([
                        Forms\Components\TextInput::make('balance_threshold')
                            ->label('Minimum Balance')
                            ->numeric()
                            ->prefix('IDR')
                            ->placeholder('Enter minimum balance'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['balance_threshold'],
                                fn(Builder $query, $threshold): Builder => $query->where('current_balance', '>=', $threshold)
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['balance_threshold'] ?? null) {
                            return 'Current balance at least $' . number_format($data['balance_threshold'], 2);
                        }
                        return null;
                    }),
                Tables\Filters\Filter::make('created_at')
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_transactions')
                    ->label('Has Transactions')
                    ->placeholder('All accounts')
                    ->trueLabel('Accounts with transactions')
                    ->falseLabel('Accounts without transactions')
                    ->queries(
                        true: fn(Builder $query) => $query->whereColumn('current_balance', '!=', 'initial_balance'),
                        false: fn(Builder $query) => $query->whereColumn('current_balance', '=', 'initial_balance'),
                    )
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('transfer')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('to_account_id')
                                ->label('To Account')
                                ->options(fn() => Accounting::pluck('account_name', 'id'))
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->prefix('IDR')
                                ->label('Amount'),
                            Forms\Components\Textarea::make('description')
                                ->label('Description')
                                ->rows(3),
                        ])
                        ->action(function (Accounting $record, array $data): void {
                            DB::transaction(function () use ($record, $data) {
                                $record->current_balance -= $data['amount'];
                                $record->save();

                                $toAccount = Accounting::findOrFail($data['to_account_id']);
                                $toAccount->current_balance += $data['amount'];
                                $toAccount->save();

                                $tenant = Filament::getTenant()->id;

                                // Create ledger record for the 'from' account
                                $fromLedger = Ledger::create([
                                    'company_id' => $tenant,
                                    'account_id' => $record->id,
                                    'transaction_date' => now(),
                                    'transaction_type' => 'credit',
                                    'amount' => $data['amount'],
                                    'transaction_description' => $data['description'] ?? 'Transfer to ' . $toAccount->account_name,
                                ]);

                                // Create ledger record for the 'to' account
                                $toLedger = Ledger::create([
                                    'company_id' => $tenant,
                                    'account_id' => $toAccount->id,
                                    'transaction_date' => now(),
                                    'transaction_type' => 'debit',
                                    'amount' => $data['amount'],
                                    'transaction_description' => $data['description'] ?? 'Transfer from ' . $record->account_name,
                                ]);

                                // Create transaction record
                                Transaction::create([
                                    'company_id' => $tenant,
                                    'ledger_id' => $fromLedger->id,
                                    'transaction_number' => 'TRF' . now()->format('YmdHis') . rand(1000, 9999),
                                    'status' => 'completed',
                                    'amount' => $data['amount'],
                                    'notes' => 'Transfer from ' . $record->account_name . ' to ' . $toAccount->account_name,
                                ]);

                                Transaction::create([
                                    'company_id' => $tenant,
                                    'ledger_id' => $toLedger->id,
                                    'transaction_number' => 'TRF' . now()->format('YmdHis') . rand(1000, 9999),
                                    'status' => 'completed',
                                    'amount' => $data['amount'],
                                    'notes' => 'Transfer from ' . $record->account_name . ' to ' . $toAccount->account_name,
                                ]);
                            });

                            Notification::make()
                                ->title('Transfer successful')
                                ->body(('Your transfer has been completed successfully.' . ' ' . now()->toDateTimeString()))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])
            ])
            ->headerActions([
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(AccountingExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Account exported successfully' . ' ' . now()->toDateTimeString())
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(AccountingImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('warning')
                        ->after(function () {
                            Notification::make()
                                ->title('Account imported successfully' . ' ' . now()->toDateTimeString())
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
                    Tables\Actions\BulkAction::make('updateBalances')
                        ->label('Update Balances')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('action')
                                ->label('Action')
                                ->options([
                                    'add' => 'Add to Balance',
                                    'subtract' => 'Subtract from Balance',
                                    'set' => 'Set Balance',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->prefix('IDR')
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Accounting $account) use ($data) {
                                switch ($data['action']) {
                                    case 'add':
                                        $account->current_balance += $data['amount'];
                                        break;
                                    case 'subtract':
                                        $account->current_balance -= $data['amount'];
                                        break;
                                    case 'set':
                                        $account->current_balance = $data['amount'];
                                        break;
                                }
                                $account->save();
                            });

                            Notification::make()
                                ->title('Balances updated successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('changeAccountType')
                        ->label('Change Account Type')
                        ->icon('heroicon-o-tag')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('account_type')
                                ->label('New Account Type')
                                ->options([
                                    'asset' => 'Asset',
                                    'liability' => 'Liability',
                                    'equity' => 'Equity',
                                    'revenue' => 'Revenue',
                                    'expense' => 'Expense',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Accounting $account) use ($data) {
                                $account->account_type = $data['account_type'];
                                $account->save();
                            });

                            Notification::make()
                                ->title('Account types changed successfully')
                                ->success()
                                ->send();
                        }),
                    ExportBulkAction::make()
                        ->exporter(AccountingExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Account exported successfully' . ' ' . now()->format('Y-m-d H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->label('Bulk Actions'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Account')
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            JournalEntriesRelationManager::class,
            LedgerRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountings::route('/'),
            'create' => Pages\CreateAccounting::route('/create'),
            'edit' => Pages\EditAccounting::route('/{record}/edit'),
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
