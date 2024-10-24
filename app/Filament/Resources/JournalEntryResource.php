<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
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
use Doctrine\DBAL\Query;
use Filament\Tables\Actions\ActionGroup;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static ?int $navigationSort = 8;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'journalEntry';

    protected static ?string $navigationGroup = 'Management Financial';

    protected static ?string $navigationIcon = 'bi-journal-bookmark-fill';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Journal Entry')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->required()
                            ->label('Branch')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('entry_date')
                            ->required()
                            ->default(now())
                            ->label('Entry Date')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->nullable()
                            ->label('Description')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('entry_type')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Credit',
                            ])
                            ->required()
                            ->label('Entry Type'),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->label('Amount')
                            ->prefix('IDR')
                            ->maxValue(429494324672.95),
                        Forms\Components\Select::make('account_id')
                            ->relationship('account', 'account_name')
                            ->required()
                            ->label('Account')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
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
            ])
            ->columns(1);
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
                    ->label('Branch')
                    ->sortable()
                    ->icon('heroicon-s-building-storefront')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Entry Date')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('entry_type')
                    ->label('Entry Type')
                    ->toggleable()
                    ->badge()
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
                Tables\Columns\TextColumn::make('account.account_name')
                    ->label('Account')
                    ->sortable()
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
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->label('Branch')
                    ->multiple()
                    ->preload(),
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
            ->headerActions([
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(JournalEntryExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Journal Entry exported successfully' . ' ' . now()->format('d-m-Y H:i:s'))
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
                                ->title('Journal Entry imported successfully' .  ' ' . now()->format('d-m-Y H:i:s'))
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
                    Tables\Actions\BulkAction::make('updateAccount')
                        ->label('Update Account')
                        ->icon('heroicon-o-building-office')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('account_id')
                                ->label('Account')
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
                        ->exporter(JournalEntryExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Account exported successfully' .  ' ' . now()->format('d-m-Y H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Journal Entry')
                    ->icon('heroicon-o-plus'),
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
