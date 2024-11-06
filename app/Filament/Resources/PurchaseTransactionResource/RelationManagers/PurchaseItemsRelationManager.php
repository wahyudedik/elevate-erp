<?php

namespace App\Filament\Resources\PurchaseTransactionResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementStock\PurchaseItem;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PurchaseItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Item Details')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),

                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('purchase_transaction_id')
                            ->relationship('purchaseTransaction', 'id', fn($query) => $query->where('status', 'pending'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select Purchase Transaction'),

                        Forms\Components\TextInput::make('product_name')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete()
                            ->placeholder('Enter product name'),

                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->step(1)
                            ->placeholder('Enter quantity'),

                        Forms\Components\TextInput::make('unit_price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('IDR')
                            ->placeholder('Enter unit price'),

                        Forms\Components\Placeholder::make('total_price')
                            ->default(function ($get) {
                                return $get('quantity') * $get('unit_price');
                            })
                            ->content(function ($get) {
                                return 'IDR ' . number_format($get('quantity') * $get('unit_price'), 2);
                            }),
                    ])->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn(?PurchaseItem $record): string => $record ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last Modified At')
                            ->content(fn(?PurchaseItem $record): string => $record ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ])->live()->reactive();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchaseTransaction.id')
                    ->label('Purchase Transaction')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product Name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
                Tables\Filters\SelectFilter::make('purchase_transaction_id')
                    ->relationship('purchaseTransaction', 'id')
                    ->label('Purchase Transaction')
                    ->searchable()
                    ->preload()
                    ->multiple(),
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
                            $indicators['created_from'] = 'Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_purchase_transaction')
                    ->label('Has Purchase Transaction')
                    ->placeholder('All items')
                    ->trueLabel('With Purchase Transaction')
                    ->falseLabel('Without Purchase Transaction')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('purchaseTransaction'),
                        false: fn(Builder $query) => $query->whereDoesntHave('purchaseTransaction'),
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('updatePrice')
                        ->label('Update Price')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->required()
                                ->numeric()
                                ->prefix('$'),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Quantity')
                                ->required()
                                ->numeric()
                                ->minValue(1),
                        ])
                        ->action(function (PurchaseItem $record, array $data): void {
                            $record->update([
                                'unit_price' => $data['unit_price'],
                                'quantity' => $data['quantity'],
                                'total_price' => $data['unit_price'] * $data['quantity'],
                            ]);
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Price updated')
                                ->body('The purchase item price has been updated successfully.')
                        ),
                    Tables\Actions\Action::make('attachTransaction')
                        ->label('Attach Transaction')
                        ->icon('heroicon-o-link')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('purchase_transaction_id')
                                ->label('Purchase Transaction')
                                ->relationship('purchaseTransaction', 'id')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (PurchaseItem $record, array $data): void {
                            $record->update([
                                'purchase_transaction_id' => $data['purchase_transaction_id'],
                            ]);
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Transaction attached')
                                ->body('The purchase item has been attached to a transaction successfully.')
                        ),

                ])
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()->icon('heroicon-o-plus'),
            ]);
    }
}
