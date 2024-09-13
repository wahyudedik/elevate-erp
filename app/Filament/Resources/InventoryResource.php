<?php

namespace App\Filament\Resources;

use App\Filament\Exports\InventoryExporter;
use App\Filament\Imports\InventoryImporter;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ManagementStock\Inventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\InventoryResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Filament\Resources\InventoryResource\RelationManagers\SupplierRelationManager;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationBadgeTooltip = 'Total Inventory';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Stock';

    protected static ?string $navigationIcon = 'polaris-inventory-icon';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Item Details')
                    ->schema([
                        Forms\Components\TextInput::make('item_name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                $set('sku', $state . '-' . now()->format('Ymd'));
                            }),
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('purchase_price')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(42949672.95),
                        Forms\Components\TextInput::make('selling_price')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(42949672.95),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'supplier_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'in_stock' => 'In Stock',
                                'out_of_stock' => 'Out of Stock',
                                'discontinued' => 'Discontinued',
                            ])
                            ->required()
                            ->default('in_stock'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('supplier.supplier_name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'in_stock' => 'In Stock',
                        'out_of_stock' => 'Out of Stock',
                        'discontinued' => 'Discontinued',
                    ])
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'in_stock' => 'In Stock',
                        'out_of_stock' => 'Out of Stock',
                        'discontinued' => 'Discontinued',
                    ])
                    ->columnSpanFull(),
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'supplier_name')
                    ->columnSpanFull(),
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', '<=', 10))
                    ->label('Low Stock')
                    ->columnSpanFull(),
                Tables\Filters\Filter::make('high_value')
                    ->query(fn(Builder $query): Builder => $query->where('selling_price', '>=', 1000))
                    ->label('High Value Items')
                    ->columnSpanFull(),
                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('created_until')
                    ->form([
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('updateQuantity')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function (Inventory $record, array $data): void {
                            $record->update(['quantity' => $data['quantity']]);
                        })
                        ->icon('heroicon-o-clipboard-document-list')
                        ->color('warning')
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('adjustPrice')
                        ->form([
                            Forms\Components\TextInput::make('purchase_price')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('selling_price')
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function (Inventory $record, array $data): void {
                            $record->update([
                                'purchase_price' => $data['purchase_price'],
                                'selling_price' => $data['selling_price'],
                            ]);
                        })
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('changeStatus')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'in_stock' => 'In Stock',
                                    'out_of_stock' => 'Out of Stock',
                                    'discontinued' => 'Discontinued',
                                ])
                                ->required(),
                        ])
                        ->action(function (Inventory $record, array $data): void {
                            $record->update(['status' => $data['status']]);
                        })
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation(),
                ])
            ])
            ->headerActions([
                ExportAction::make()->exporter(InventoryExporter::class),
                ImportAction::make()->importer(InventoryImporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateQuantityBulk')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update(['quantity' => $data['quantity']]);
                            });
                        })
                        ->icon('heroicon-o-clipboard-document-list')
                        ->color('warning')
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('adjustPriceBulk')
                        ->form([
                            Forms\Components\TextInput::make('purchase_price')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('selling_price')
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'purchase_price' => $data['purchase_price'],
                                    'selling_price' => $data['selling_price'],
                                ]);
                            });
                        })
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('changeStatusBulk')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'in_stock' => 'In Stock',
                                    'out_of_stock' => 'Out of Stock',
                                    'discontinued' => 'Discontinued',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation(),
                ]),
                ExportBulkAction::make()->exporter(InventoryExporter::class),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SupplierRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
