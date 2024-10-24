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

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'transaction';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->required()
                            ->searchable()
                            ->preload(),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ledger_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-building-storefront')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ledger.transaction_date')
                    ->label('Ledger')
                    ->sortable()
                    ->date('d F Y')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Transaction Number')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Count::make()
                            ->label('Items')
                    ]),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'danger' => 'failed',
                        'warning' => 'pending',
                        'success' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('IDR')
                    ])
                    ->copyable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->toggleable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
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
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
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
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('changeStatus')
                        ->label('Change Status')
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
                                ->title('Status updated successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('addNote')
                        ->label('Add Note')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->required()
                                ->maxLength(65535),
                        ])
                        ->action(function (Transaction $record, array $data) {
                            $record->update(['notes' => $data['notes']]);
                            Notification::make()
                                ->title('Note added successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('printReceipt')
                        ->label('Print Receipt')
                        ->icon('heroicon-o-printer')
                        ->url(fn(Transaction $record) => route('transaction.print-receipt', $record))
                        ->openUrlInNewTab()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('changeStatus')
                        ->label('Change Status')
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
                        ->label('Add Note')
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
}
