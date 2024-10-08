<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PurchaseItemExporter;
use App\Filament\Imports\PurchaseItemImporter;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PurchaseItemResource\Pages;
use App\Models\ManagementSalesAndPurchasing\PurchaseItem;
use App\Filament\Resources\PurchaseItemResource\RelationManagers;
use App\Filament\Resources\PurchaseItemResource\RelationManagers\PurchaseTransactionRelationManager;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;

class PurchaseItemResource extends Resource
{
    protected static ?string $model = PurchaseItem::class;

    protected static ?string $navigationBadgeTooltip = 'Total Purchase Transactions';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Sales And Purchasing';

    protected static ?string $navigationParentItem = 'Purchase Transactions';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('purchase_transaction_id')
                    ->relationship('purchaseTransaction', 'id')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
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
            ])
            ->filters([
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
                ExportAction::make()->exporter(PurchaseItemExporter::class),
                ImportAction::make()->importer(PurchaseItemImporter::class)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()->exporter(PurchaseItemExporter::class)
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PurchaseTransactionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseItems::route('/'),
            'create' => Pages\CreatePurchaseItem::route('/create'),
            'edit' => Pages\EditPurchaseItem::route('/{record}/edit'),
        ];
    }
}
