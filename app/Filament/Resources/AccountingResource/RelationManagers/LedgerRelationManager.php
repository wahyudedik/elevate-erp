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

    protected static ?string $title = 'Buku Besar';

    protected static ?string $label = 'Buku Besar';

    protected static ?string $pluralLabel = 'Buku Besar';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ledger')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->required()
                            ->format('Y-m-d')
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->columnSpanFull()
                            ->native(false),
                        Forms\Components\Select::make('transaction_type')
                            ->label('Jenis Transaksi')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Credit',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->step(2)
                            ->mask('999999999999999.99')
                            ->prefix('IDR')
                            ->required(),
                        Forms\Components\Textarea::make('transaction_description')
                            ->label('Deskripsi Transaksi')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat Pada')
                            ->default('-')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir Diperbarui')
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
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-s-building-storefront')
                    ->toggleable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->toggleable()
                    ->searchable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->label('Tipe')
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
                    ->toggleable()
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
                    ->searchable()
                    ->toggleable()
                    ->tooltip(fn($record) => $record->transaction_description)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->size('sm'),
            ])
            ->filters([
                Tables\Filters\Filter::make('transaction_date')
                    ->label('Tanggal Transaksi')
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
                    ->label('Tipe Transaksi'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
