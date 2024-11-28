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
                Forms\Components\Section::make('Journal Entry')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('entry_date')
                            ->required()
                            ->label('Tanggal Entri')
                            ->default(now())
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('entry_type')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Kredit',
                            ])
                            ->required()
                            ->label('Jenis Entri'),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->label('Jumlah')
                            ->prefix('IDR')
                            ->maxValue(429496976772.95)
                            ->minValue(0)
                            ->step(0.01),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Di Buat Pada')
                            ->default('-')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Di Ubah Pada')
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
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->icon('heroicon-s-building-storefront')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('entry_date')
                    ->date()
                    ->icon('heroicon-o-calendar')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('entry_type')
                    ->icon(fn(string $state): string => match ($state) {
                        'credit' => 'heroicon-o-arrow-up-circle',
                        'debit' => 'heroicon-o-arrow-down-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->colors([
                        'danger' => 'credit',
                        'success' => 'debit',
                    ])
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('account.account_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Branch')
                    ->indicator('Branch'),
                Tables\Filters\SelectFilter::make('entry_type')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                    ])
                    ->label('Entry Type')
                    ->indicator('Entry Type'),

                Tables\Filters\Filter::make('amount')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->label('From')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('amount_to')
                            ->label('To')
                            ->numeric()
                            ->prefix('IDR'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn(Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn(Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['amount_from'] ?? null) {
                            $indicators['amount_from'] = 'Amount from: IDR ' . number_format($data['amount_from'], 2);
                        }
                        if ($data['amount_to'] ?? null) {
                            $indicators['amount_to'] = 'Amount to: IDR ' . number_format($data['amount_to'], 2);
                        }
                        return $indicators;
                    })->columns(2),

                Tables\Filters\SelectFilter::make('account_id')
                    ->relationship('account', 'account_name')
                    ->label('Account')
                    ->indicator('Account'),

                Tables\Filters\TernaryFilter::make('description')
                    ->label('Has Description')
                    ->nullable()
                    ->placeholder('All entries')
                    ->trueLabel('With description')
                    ->falseLabel('Without description')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('description'),
                        false: fn(Builder $query) => $query->whereNull('description'),
                    )
                    ->indicator('Description Status'),
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
                        ->url(fn(JournalEntry $record): string => route('journal-entries.print', $record))
                        ->openUrlInNewTab(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateEntryType')
                        ->label('Update Entry Type')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('entry_type')
                                ->label('Entry Type')
                                ->options([
                                    'debit' => 'Debit',
                                    'credit' => 'Credit',
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
                        ->label('Update Amount')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->prefix('IDR')
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
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function beforeCreate(array $data): array
    {
        if (!isset($data['company_id']) && Auth::check()) {
            $data['company_id'] = DB::table('company_user')
                ->where('user_id', Auth::user()->id)
                ->value('company_id');
        }
        dd($data);
        return $data;
    }
}
