<?php

namespace App\Filament\Resources\AccountingResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use PhpParser\Node\Stmt\Return_;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\ManagementFinancial\Accounting;
use App\Models\ManagementFinancial\JournalEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\ActionGroup;

class JournalEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'journalEntries';

    protected static ?string $title = 'Jurnal Umum';

    protected static ?string $label = 'Jurnal Umum';

    protected static ?string $pluralLabel = 'Jurnal Umum';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Entri Jurnal')
                    ->description('Silakan lengkapi informasi entri jurnal berikut')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang Perusahaan')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih cabang tempat transaksi berlangsung')
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('entry_date')
                            ->required()
                            ->label('Tanggal Transaksi')
                            ->default(now())
                            ->helperText('Masukkan tanggal transaksi dilakukan')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description')
                            ->label('Keterangan Transaksi')
                            ->helperText('Tuliskan detail keterangan transaksi')
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
                            ->helperText('Pilih jenis transaksi yang sesuai')
                            ->icon('heroicon-m-calculator')
                            ->native(false),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->label('Nominal Transaksi')
                            ->prefix('Rp')
                            ->maxValue(429496976772.95)
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Masukkan jumlah nominal dalam Rupiah')
                            ->icon('heroicon-m-banknotes'),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->description('Detail waktu pembuatan dan perubahan data')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Waktu Pembuatan')
                            ->default('-')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-')
                            ->icon('heroicon-o-clock'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->default('-')
                            ->content(fn($record): string => $record?->updated_at ? $record->updated_at->diffForHumans() : '-')
                            ->icon('heroicon-o-arrow-path'),
                    ])
                    ->columns(2)
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
                    ->alignCenter()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang Perusahaan')
                    ->sortable()
                    ->icon('heroicon-s-building-office-2')
                    ->searchable()
                    ->toggleable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal Transaksi')
                    ->date('d F Y')
                    ->icon('heroicon-m-calendar-days')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(50)
                    ->html()
                    ->searchable()
                    ->toggleable()
                    ->wrap()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('entry_type')
                    ->label('Jenis')
                    ->icon(fn(string $state): string => match ($state) {
                        'credit' => 'heroicon-m-arrow-trending-up',
                        'debit' => 'heroicon-m-arrow-trending-down',
                        default => 'heroicon-m-exclamation-circle',
                    })
                    ->colors([
                        'danger' => 'credit',
                        'success' => 'debit',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'credit' => 'Kredit',
                        'debit' => 'Debit',
                        default => 'Tidak Diketahui',
                    })
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', true)
                    ->alignment('right')
                    ->sortable()
                    ->toggleable()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('account.account_name')
                    ->label('Akun')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d F Y, H:i')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d F Y, H:i')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->color('gray'),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('entry_type')
                    ->label('Filter Jenis Transaksi')
                    ->options([
                        'credit' => 'Kredit',
                        'debit' => 'Debit',
                    ])
                    ->indicator('Jenis'),
                Tables\Filters\Filter::make('entry_date')
                    ->label('Filter Periode Transaksi')
                    ->indicator('Periode')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal awal')
                            ->prefixIcon('heroicon-m-calendar-days'),
                        Forms\Components\DatePicker::make('until_date')
                            ->label('Hingga Tanggal')
                            ->placeholder('Pilih tanggal akhir')
                            ->prefixIcon('heroicon-m-calendar-days'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('entry_date', '>=', $date),
                            )
                            ->when(
                                $data['until_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('entry_date', '<=', $date),
                            );
                    })->columns(2),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Rincian')
                    ->tooltip('Tampilkan detail transaksi')
                    ->icon('heroicon-m-eye')
                    ->color('primary'),
            ]);
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->withoutGlobalScopes([
    //             SoftDeletingScope::class,
    //         ]);
    // }

    // public static function beforeCreate(array $data): array
    // {
    //     if (!isset($data['company_id']) && Auth::check()) {
    //         $data['company_id'] = DB::table('company_user')
    //             ->where('user_id', Auth::user()->id)
    //             ->value('company_id');
    //     }
    //     dd($data);
    //     return $data;
    // }
}
