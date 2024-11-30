<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Barryvdh\DomPDF\PDF;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Filament\Exports\LedgerExporter;
use App\Filament\Imports\LedgerImporter;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementFinancial\Ledger;
use App\Filament\Clusters\Ledger as ledgers;
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

    protected static ?string $navigationLabel = 'Buku Besar';

    protected static ?string $modelLabel = 'Buku Besar';

    protected static ?string $pluralModelLabel = 'Buku Besar';

    protected static ?string $cluster = ledgers::class;

    protected static ?int $navigationSort = 9;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'ledger';

    protected static ?string $navigationGroup = 'Buku Besar';

    protected static ?string $navigationIcon = 'tabler-report-analytics';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Buku Besar Data')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->native(false)
                            ->placeholder('Pilih Cabang')
                            ->label('Cabang'),
                        Forms\Components\Select::make('account_id')
                            ->relationship(
                                'account',
                                'account_name',
                                fn(Builder $query, $get) =>
                                $query->when(
                                    $get('branch_id'),
                                    fn($query, $branch_id) =>
                                    $query->where('branch_id', $branch_id)
                                )
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Pilih Akun')
                            ->label('Akun')
                            ->disabled(fn($get) => ! $get('branch_id')),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->required()
                            ->default(now())
                            ->label('Tanggal Transaksi'),
                        Forms\Components\Select::make('transaction_type')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Kredit',
                            ])
                            ->required()
                            ->label('Jenis Transaksi'),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->rule('decimal:0,2')
                            ->label('Jumlah'),
                        Forms\Components\RichEditor::make('transaction_description')
                            ->nullable()
                            ->columnSpanFull()
                            ->label('Deskripsi Transaksi'),
                    ])->columns(2),
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
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-building-storefront')
                    ->searchable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('account.account_name')
                    ->label('Akun')
                    ->sortable()
                    ->toggleable()
                    ->wrap()
                    ->searchable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->date()
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->toggleable()
                    ->label('Jenis Transaksi')
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
                    ->searchable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->searchable()
                    ->alignment('right')
                    ->weight('bold')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('transaction_description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap()
                    ->html()
                    ->toggleable()
                    ->searchable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name', fn($query) => $query->where('status', ['active', 'inactive']))
                    ->searchable()
                    ->label('Cabang')
                    ->preload()
                    ->label('Branch'),
                Tables\Filters\SelectFilter::make('account')
                    ->relationship('account', 'account_name')
                    ->searchable()
                    ->label('Akun')
                    ->preload()
                    ->label('Account'),
                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
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
                        ->action(function (Ledger $record) {
                            $data = [
                                'ledger' => $record,
                                'transactions' => $record->transactions()
                                    ->with(['company', 'branch'])
                                    ->orderBy('created_at', 'desc')
                                    ->get(),
                                'totalPending' => $record->transactions()
                                    ->where('status', 'pending')
                                    ->sum('amount'),
                                'totalCompleted' => $record->transactions()
                                    ->where('status', 'completed')
                                    ->sum('amount'),
                                'totalFailed' => $record->transactions()
                                    ->where('status', 'failed')
                                    ->sum('amount'),
                                'totalAmount' => $record->transactions()
                                    ->where('status', 'completed')
                                    ->sum('amount')
                            ];

                            $pdf = app('dompdf.wrapper')->loadView('pdf.ledger', $data);
                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, 'ledger-' . $record->id . '.pdf');
                        })
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Buku Besar Baru')
                    ->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(LedgerExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Buku Besar berhasil diekspor' . ' ' . now()->toDateTimeString())->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(LedgerImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Buku Besar berhasil diimpor' . ' ' . now()->toDateTimeString())->icon('heroicon-o-check-circle')
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
                        ->label('Ubah Tipe Transaksi')
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
                        ->label('Ubah Tanggal Transaksi')
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
                                ->title('Buku Besar berhasil diekspor' . ' ' . now()->format('Y-m-d H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Buku Besar Baru')
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

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'company_id',
            'branch_id',
            'account_id',
            'transaction_date',
            'transaction_type',
            'amount',
            'transaction_description',
        ];
    }
}
