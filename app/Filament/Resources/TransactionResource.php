<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Exports\TransactionExporter;
use App\Filament\Imports\TransactionImporter;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementFinancial\Transaction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Filament\Resources\TransactionResource\RelationManagers\LedgerRelationManager;
use App\Filament\Clusters\Ledger as ledgers;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\LinkAction;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationLabel = 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?string $pluralModelLabel = 'Transaksi';

    protected static ?int $navigationSort = 10;

    protected static ?string $cluster = ledgers::class;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'transaction';

    protected static ?string $navigationGroup = 'Buku Besar';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Transaksi')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'Active'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Cabang'),
                        Forms\Components\Select::make('ledger_id')
                            ->relationship('ledger', 'transaction_date')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\Hidden::make('company_id')
                                    ->default(Filament::getTenant()->id),
                                Forms\Components\Select::make('branch_id')
                                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'Active'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->label('Cabang'),
                                Forms\Components\Select::make('account_id')
                                    ->relationship('account', 'account_name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->label('Akun'),
                                Forms\Components\DatePicker::make('transaction_date')
                                    ->required()
                                    ->label('Tanggal Transaksi'),
                                Forms\Components\Select::make('transaction_type')
                                    ->options([
                                        'debit' => 'Debit',
                                        'credit' => 'Kredit',
                                    ])
                                    ->required()
                                    ->label('Tipe Transaksi'),
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->required()
                                    ->rule('decimal:0,2')
                                    ->label('Jumlah'),
                                Forms\Components\Textarea::make('transaction_description')
                                    ->nullable()
                                    ->columnSpanFull()
                                    ->label('Deskripsi Transaksi'),
                            ])
                            ->label('Buku Besar'),
                        Forms\Components\TextInput::make('transaction_number')
                            ->required()
                            ->readOnly()
                            ->maxLength(255)
                            ->default(function () {
                                return 'TRX-' . strtoupper(uniqid());
                            })
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(9999999999999.99),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
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
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary')
                    ->searchable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('ledger.transaction_date')
                    ->label('Buku Besar')
                    ->sortable()
                    ->date()
                    ->toggleable()
                    ->searchable()
                    ->size('sm')
                    ->icon('heroicon-o-calendar')
                    ->iconColor('success'),
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Nomor Transaksi')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->size('sm')
                    ->icon('heroicon-o-hashtag')
                    ->iconColor('warning')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->toggleable()
                    ->size('sm')
                    ->colors([
                        'danger' => 'failed',
                        'warning' => 'pending',
                        'success' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->size('sm')
                    ->icon('heroicon-o-currency-dollar')
                    ->iconColor('danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->toggleable()
                    ->size('sm')
                    ->icon('heroicon-o-document-text')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true)
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                    ->label('Cabang')
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->multiple()
                    ->label('Status'),
                Tables\Filters\TernaryFilter::make('has_notes')
                    ->label('Has Notes')
                    ->queries(
                        true: fn($query) => $query->whereNotNull('notes'),
                        false: fn($query) => $query->whereNull('notes'),
                    ),
                Tables\Filters\Filter::make('created_range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dibuat Dari'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Dibuat Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn($query, $date) => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn($query, $date) => $query->whereDate('created_at', '<=', $date)
                            );
                    })->columns(2)
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('changeStatus')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'completed' => 'Completed',
                                    'failed' => 'Failed',
                                ])
                                ->required(),
                        ])
                        ->action(function (Transaction $record, array $data) {
                            $record->update(['status' => $data['status']]);
                            Notification::make()
                                ->title('Status berhasil diperbarui')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('addNote')
                        ->label('Tambah Catatan')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->required()
                                ->maxLength(65535),
                        ])
                        ->action(function (Transaction $record, array $data) {
                            $record->update(['notes' => $data['notes']]);
                            Notification::make()
                                ->title('Catatan berhasil ditambahkan')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('printReceipt')
                        ->label('Cetak Kwitansi')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->action(function (Transaction $record) {
                            $pdf = app('dompdf.wrapper')->loadView('pdf.receipt', ['transaction' => $record]);
                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, 'receipt-' . $record->id . '.pdf');
                        })
                ])
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Transaksi Baru')
                    ->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(TransactionExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Transaksi berhasil diekspor' . ' ' . now()->toDateTimeString())->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(TransactionImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Transaksi berhasil diimpor' . ' ' . now()->toDateTimeString())->icon('heroicon-o-check-circle')
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
                    Tables\Actions\BulkAction::make('changeStatus')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'completed' => 'Completed',
                                    'failed' => 'Failed',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                            Notification::make()
                                ->title('Status updated successfully for selected records')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('addNote')
                        ->label('Tambah Catatan')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->required()
                                ->maxLength(65535),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['notes' => $data['notes']]);
                            });
                            Notification::make()
                                ->title('Note added successfully to selected records')
                                ->success()
                                ->send();
                        }),
                    ExportBulkAction::make()
                        ->exporter(TransactionExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Transaksi berhasil diekspor' . ' ' . now()->format('Y-m-d H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Transaksi Baru')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LedgerRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
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
            'ledger_id',
            'transaction_number',
            'status',
            'amount',
            'notes',
        ];
    }
}
