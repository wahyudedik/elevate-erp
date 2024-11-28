<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Branch;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use OpenSpout\Writer\CSV\Writer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
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
use App\Models\ManagementFinancial\JournalEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AccountingResource\Pages;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Filament\Resources\AccountingResource\RelationManagers;
use App\Filament\Resources\AccountingResource\RelationManagers\LedgerRelationManager;
use App\Filament\Resources\AccountingResource\RelationManagers\JournalEntriesRelationManager;

class AccountingResource extends Resource
{
    protected static ?string $model = Accounting::class;

    protected static ?string $navigationLabel = 'Akuntansi';

    protected static ?string $modelLabel = 'Akuntansi';

    protected static ?string $pluralModelLabel = 'Akuntansi';

    protected static ?int $navigationSort = 7;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'accounting';

    protected static ?string $navigationGroup = 'Manajemen Keuangan';

    protected static ?string $navigationIcon = 'mdi-finance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('account_name')
                            ->label('Nama Akun')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_number')
                            ->label('Nomor Akun')
                            ->required()
                            ->unique(ignorable: fn($record) => $record)
                            ->maxLength(255),
                        Forms\Components\Select::make('account_type')
                            ->label('Tipe Akun')
                            ->required()
                            ->options([
                                'asset' => 'Asset / Aset',
                                'liability' => 'Liability / Kewajiban',
                                'equity' => 'Equity / Modal',
                                'revenue' => 'Revenue / Pendapatan',
                                'expense' => 'Expense / Beban',
                            ]),
                        Forms\Components\TextInput::make('initial_balance')
                            ->label('Saldo Awal')
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
                            ->label('Saldo Akhir')
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
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat pada')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir diubah pada')
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
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-building-storefront')
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_name')
                    ->searchable()
                    ->label('Nama Akun')
                    ->limit(50)
                    ->wrap()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->searchable()
                    ->label('Nomor Akun')
                    ->toggleable()
                    ->sortable()
                    ->icon('heroicon-o-hashtag'),
                Tables\Columns\TextColumn::make('account_type')
                    ->icon('heroicon-o-currency-dollar')
                    ->badge()
                    ->label('Tipe Akun')
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
                    ->label('Saldo Awal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->money('IDR')
                    ->toggleable()
                    ->label('Saldo Akhir')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Dibuat pada')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Terakhir diubah pada')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->label('Branch')
                    ->multiple()
                    ->preload(),
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
                                ->label('Akun Penerima')
                                ->options(fn() => Accounting::pluck('account_name', 'id'))
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->numeric()
                                ->label('Jumlah')
                                ->required()
                                ->minValue(0.01)
                                ->prefix('IDR'),
                            Forms\Components\Textarea::make('description')
                                ->label('Deskripsi')
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
                                $branch = Branch::where('company_id', $tenant)->first()->id;

                                // Create ledger record for the 'from' account
                                $fromLedger = Ledger::create([
                                    'company_id' => $tenant,
                                    'branch_id' => $branch,
                                    'account_id' => $record->id,
                                    'transaction_date' => now(),
                                    'transaction_type' => 'credit',
                                    'amount' => $data['amount'],
                                    'transaction_description' => $data['description'] ?? 'Transfer to ' . $toAccount->account_name,
                                ]);

                                // Create ledger record for the 'to' account
                                $toLedger = Ledger::create([
                                    'company_id' => $tenant,
                                    'branch_id' => $branch,
                                    'account_id' => $toAccount->id,
                                    'transaction_date' => now(),
                                    'transaction_type' => 'debit',
                                    'amount' => $data['amount'],
                                    'transaction_description' => $data['description'] ?? 'Transfer from ' . $record->account_name,
                                ]);

                                // Create transaction record
                                Transaction::create([
                                    'company_id' => $tenant,
                                    'branch_id' => $branch,
                                    'ledger_id' => $fromLedger->id,
                                    'transaction_number' => 'TRF' . now()->format('YmdHis') . rand(1000, 9999),
                                    'status' => 'completed',
                                    'amount' => $data['amount'],
                                    'notes' => 'Transfer from ' . $record->account_name . ' to ' . $toAccount->account_name,
                                ]);

                                Transaction::create([
                                    'company_id' => $tenant,
                                    'branch_id' => $branch,
                                    'ledger_id' => $toLedger->id,
                                    'transaction_number' => 'TRF' . now()->format('YmdHis') . rand(1000, 9999),
                                    'status' => 'completed',
                                    'amount' => $data['amount'],
                                    'notes' => 'Transfer from ' . $record->account_name . ' to ' . $toAccount->account_name,
                                ]);

                                // Create journal entry
                                // $journalEntry = JournalEntry::create([
                                //     'company_id' => $tenant,
                                //     'branch_id' => $branch,
                                //     'entry_date' => now(),
                                //     'description' => $data['description'] ?? 'Transfer between accounts',
                                //     'entry_type' => 'credit',
                                //     'amount' => $data['amount'],
                                //     'account_id' => $record->id,
                                // ]);

                                // $journalEntry = JournalEntry::create([
                                //     'company_id' => $tenant,
                                //     'branch_id' => $branch,
                                //     'entry_date' => now(),
                                //     'description' => $data['description'] ?? 'Transfer between accounts',
                                //     'entry_type' => 'debit',
                                //     'amount' => $data['amount'],
                                //     'account_id' => $toAccount->id,
                                // ]);
                            });
                            Notification::make()
                                ->title('Transfer successful')
                                ->body('Transfer from ' . $record->account_name . ' to ' . $data['to_account_id'] . ' for amount ' . number_format($data['amount']) . ' completed successfully at ' . now()->toDateTimeString())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Akun Baru')
                    ->icon('heroicon-o-plus'),
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
                        ->color('info')
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
                        ->label('Perbarui Saldo')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('action')
                                ->label('Action')
                                ->options([
                                    'add' => 'Tambah ke Saldo',
                                    'subtract' => 'Kurangi dari Saldo',
                                    'set' => 'Atur Saldo',
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
                                ->title('Saldo berhasil diperbarui')
                                ->body('Saldo berhasil diperbarui pada ' . now()->toDateTimeString())
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('changeAccountType')
                        ->label('Ubah Tipe Akun')
                        ->icon('heroicon-o-tag')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('account_type')
                                ->label('New Account Type')
                                ->options([
                                    'asset' => 'Asset / Aset',
                                    'liability' => 'Liability / Kewajiban',
                                    'equity' => 'Equity / Modal',
                                    'revenue' => 'Revenue / Pendapatan',
                                    'expense' => 'Expense / Beban',
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
                    ->label('Buat Akun Baru')
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

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'account_name',
            'account_number',
            'account_type', //asset, liability, equity, revenue, expense
            'initial_balance',
            'current_balance',
        ];
    }
}
