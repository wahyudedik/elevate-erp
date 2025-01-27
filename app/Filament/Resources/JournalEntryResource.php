<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Barryvdh\DomPDF\PDF;
use Doctrine\DBAL\Query;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\JournalEntryExporter;
use App\Filament\Imports\JournalEntryImporter;
use App\Models\ManagementFinancial\JournalEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\JournalEntryResource\Pages;
use App\Filament\Resources\JournalEntryResource\RelationManagers;
use App\Filament\Resources\JournalEntryResource\RelationManagers\AccountRelationManager;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static ?string $navigationLabel = 'Journal Entry';

    protected static ?string $modelLabel = 'Journal Entry';

    protected static ?string $pluralModelLabel = 'Journal Entry';

    protected static ?int $navigationSort = 8;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'journalEntry';

    protected static ?string $navigationGroup = 'Manajemen Keuangan';

    protected static ?string $navigationIcon = 'bi-journal-bookmark-fill';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Entri Jurnal')
                    ->description('Masukkan informasi transaksi jurnal')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->required()
                            ->label('Cabang')
                            ->helperText('Pilih cabang tempat transaksi')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('entry_date')
                            ->required()
                            ->default(now())
                            ->label('Tanggal Transaksi')
                            ->helperText('Pilih tanggal transaksi jurnal')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description')
                            ->nullable()
                            ->label('Keterangan Transaksi')
                            ->helperText('Masukkan detail keterangan transaksi')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                            ])
                            ->columnSpanFull(),
                        Forms\Components\Select::make('entry_type')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Kredit',
                            ])
                            ->required()
                            ->label('Jenis Transaksi')
                            ->helperText('Pilih jenis transaksi')
                            ->native(false),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->label('Nominal')
                            ->helperText('Masukkan jumlah nominal transaksi')
                            ->prefix('Rp')
                            ->suffixIcon('heroicon-m-banknotes')
                            ->maxValue(429494324672.95),
                        Forms\Components\Select::make('account_id')
                            ->relationship('account', 'account_name', fn($query, $get) => $query->where('branch_id', $get('branch_id')))
                            ->required()
                            ->label('Akun')
                            ->helperText('Pilih akun untuk transaksi')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->description('Detail waktu pembuatan dan perubahan data')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat Pada')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->content(fn($record): string => $record?->updated_at ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Nomor')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->size('sm')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Kantor Cabang')
                    ->sortable()
                    ->icon('heroicon-s-building-office-2')
                    ->toggleable()
                    ->searchable()
                    ->size('sm')
                    ->color('info'),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal Pencatatan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->searchable()
                    ->size('sm')
                    ->color('success'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan Transaksi')
                    ->limit(50)
                    ->html()
                    ->searchable()
                    ->toggleable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('entry_type')
                    ->label('Jenis Transaksi')
                    ->toggleable()
                    ->badge()
                    ->icon(fn(string $state): string => match ($state) {
                        'credit' => 'heroicon-o-arrow-trending-up',
                        'debit' => 'heroicon-o-arrow-trending-down',
                        default => 'heroicon-o-exclamation-circle',
                    })
                    ->colors([
                        'success' => 'debit',
                        'danger' => 'credit',
                    ])
                    ->sortable()
                    ->searchable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal Transaksi')
                    ->money('IDR', true)
                    ->sortable()
                    ->toggleable()
                    ->searchable()
                    ->alignment('right')
                    ->size('sm')
                    ->weight('bold')
                    ->color('primary')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR', true)
                            ->label('Total')
                    ]),
                Tables\Columns\TextColumn::make('account.account_name')
                    ->label('Nama Akun')
                    ->description(fn($record) => $record->branch->name)
                    ->sortable()
                    ->toggleable()
                    ->searchable()
                    ->size('sm')
                    ->wrap()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Pembuatan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Waktu Pembaruan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('branch.name')
                    ->label('Cabang')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Arsip')
                    ->indicator('Status Arsip'),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                    ->label('Cabang')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->indicator('Cabang')
                    ->columnSpanFull(),
                Tables\Filters\SelectFilter::make('entry_type')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Kredit',
                    ])
                    ->label('Jenis Transaksi')
                    ->indicator('Jenis Transaksi')
                    ->searchable()
                    ->native(false),
                Tables\Filters\SelectFilter::make('account_id')
                    ->relationship('account', 'account_name', fn($query) => $query->selectRaw("CONCAT(accounts.id, ' - ', accounts.account_name, ' - ', branch.name) as account_name")
                        ->leftJoin('branches as branch', 'accounts.branch_id', '=', 'branch.id')
                        ->select('accounts.*', DB::raw("CONCAT(accounts.id, ' - ', accounts.account_name, ' - ', branch.name) as account_name")))
                    ->label('Akun Perkiraan')
                    ->indicator('Akun Perkiraan')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Hapus Permanen')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Pulihkan Data')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                    Tables\Actions\EditAction::make()
                        ->label('Sunting Data')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary'),
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Rincian')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('info'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning'),
                    Tables\Actions\Action::make('print')
                        ->label('Unduh PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (JournalEntry $record) {
                            $pdf = app('dompdf.wrapper')->loadView('pdf.journal-entry', ['journalEntry' => $record]);
                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, 'jurnal-' . $record->id . '.pdf');
                        })
                ])->tooltip('Menu Tindakan')
                    ->color('gray')
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Jurnal Baru')
                    ->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(JournalEntryExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Jurnal berhasil diekspor' . ' ' . now()->format('d-m-Y H:i:s'))
                                ->success()
                                ->icon('heroicon-o-check-circle')
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(JournalEntryImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Jurnal berhasil diimpor' .  ' ' . now()->format('d-m-Y H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                ])->icon('heroicon-o-cog-6-tooth'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info'),
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning'),
                    Tables\Actions\BulkAction::make('updateEntryType')
                        ->label('Perbarui Jenis Transaksi')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('entry_type')
                                ->label('Jenis Transaksi')
                                ->options([
                                    'debit' => 'Debit',
                                    'credit' => 'Kredit',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'entry_type' => $data['entry_type'],
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('updateAmount')
                        ->label('Perbarui Nominal')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('amount')
                                ->label('Nominal')
                                ->numeric()
                                ->prefix('Rp')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'amount' => (float) $data['amount'],
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('updateAccount')
                        ->label('Perbarui Akun')
                        ->icon('heroicon-o-building-library')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('account_id')
                                ->label('Pilih Akun')
                                ->relationship('account', 'account_name')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'account_id' => $data['account_id'],
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    ExportBulkAction::make()
                        ->label('Ekspor Data')
                        ->exporter(JournalEntryExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Data jurnal berhasil diekspor pada ' . now()->format('d-m-Y H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->tooltip('Tindakan Massal'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Jurnal Baru')
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AccountRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalEntries::route('/'),
            'create' => Pages\CreateJournalEntry::route('/create'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
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
            'entry_date',
            'description',
            'entry_type',
            'amount',
            'account_id',
        ];
    }
}
