<?php

namespace App\Filament\Resources\LedgerResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\ManagementFinancial\Transaction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Transaksi';

    protected static ?string $label = 'Transaksi';

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'transaction';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaksi Detail')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->required()
                            ->label('Cabang')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('transaction_number')
                            ->required()
                            ->label('Nomor Transaksi')
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
                            ->label('Jumlah')
                            ->prefix('IDR')
                            ->maxValue(9999999999999.99),
                        Forms\Components\RichEditor::make('notes')
                            ->label('Catatan')
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ledger_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-building-storefront')
                    ->toggleable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('ledger.transaction_date')
                    ->label('Buku Besar')
                    ->sortable()
                    ->date('d F Y')
                    ->toggleable()
                    ->searchable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Nomor Transaksi')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->color('primary')
                    ->weight('medium')
                    ->size('sm')
                    ->summarize([
                        Tables\Columns\Summarizers\Count::make()
                            ->label('Total Item')
                    ]),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->size('sm')
                    ->colors([
                        'danger' => 'failed',
                        'warning' => 'pending',
                        'success' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable()
                    ->label('Jumlah')
                    ->toggleable()
                    ->alignment('right')
                    ->weight('medium')
                    ->size('sm')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('IDR')
                    ])
                    ->copyable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->html()
                    ->toggleable()
                    ->size('sm')
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
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Transaksi Baru')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
