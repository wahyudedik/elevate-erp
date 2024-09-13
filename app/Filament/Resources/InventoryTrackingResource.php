<?php

namespace App\Filament\Resources;

use App\Filament\Exports\InventoryTrackingExporter;
use App\Filament\Imports\InventoryTrackingImporter;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use App\Models\ManagementStock\Inventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\ManagementStock\InventoryTracking;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InventoryTrackingResource\Pages;
use App\Filament\Resources\InventoryTrackingResource\RelationManagers;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;

class InventoryTrackingResource extends Resource
{
    protected static ?string $model = InventoryTracking::class;

    protected static ?string $navigationBadgeTooltip = 'Total Inventory';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Stock';

    protected static ?string $navigationParentItem = 'Inventories';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('inventory_id')
                    ->relationship('inventory', 'item_name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
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
                    ]),
                Forms\Components\TextInput::make('quantity_before')
                    ->required()
                    ->numeric()
                    ->integer(),
                Forms\Components\TextInput::make('quantity_after')
                    ->required()
                    ->numeric()
                    ->integer(),
                Forms\Components\Select::make('transaction_type')
                    ->options([
                        'addition' => 'Addition',
                        'deduction' => 'Deduction',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('remarks')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('transaction_date')
                    ->default(now())
                    ->required(),
            ])
            ->columns(2);
        // ->inlineLabel(1);
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
                Tables\Columns\TextColumn::make('inventory.item_name')
                    ->label('Inventory')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity_before')
                    ->label('Quantity Before')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity_after')
                    ->label('Quantity After')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->toggleable()
                    ->label('Transaction Type')
                    ->colors([
                        'success' => 'addition',
                        'danger' => 'deduction',
                    ])
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(50)
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Transaction Date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('inventory')
                    ->relationship('inventory', 'item_name')
                    ->searchable()
                    ->preload()
                    ->label('Inventory'),
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->options([
                        'addition' => 'Addition',
                        'deduction' => 'Deduction',
                    ])
                    ->label('Transaction Type'),
                Tables\Filters\Filter::make('quantity_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('quantity_from')
                                    ->numeric()
                                    ->label('Quantity From'),
                                Forms\Components\TextInput::make('quantity_to')
                                    ->numeric()
                                    ->label('Quantity To'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['quantity_from'],
                                fn(Builder $query, $value): Builder => $query->where('quantity_after', '>=', $value)
                            )
                            ->when(
                                $data['quantity_to'],
                                fn(Builder $query, $value): Builder => $query->where('quantity_after', '<=', $value)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['quantity_from'] ?? null) {
                            $indicators['quantity_from'] = 'Quantity from: ' . $data['quantity_from'];
                        }
                        if ($data['quantity_to'] ?? null) {
                            $indicators['quantity_to'] = 'Quantity to: ' . $data['quantity_to'];
                        }
                        return $indicators;
                    }),
                Tables\Filters\Filter::make('transaction_date_range')
                    ->form([
                        Forms\Components\DatePicker::make('transaction_date_from')
                            ->label('Transaction Date From'),
                        Forms\Components\DatePicker::make('transaction_date_to')
                            ->label('Transaction Date To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['transaction_date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date)
                            )
                            ->when(
                                $data['transaction_date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['transaction_date_from'] ?? null) {
                            $indicators['transaction_date_from'] = 'Transaction date from: ' . Carbon::parse($data['transaction_date_from'])->toFormattedDateString();
                        }
                        if ($data['transaction_date_to'] ?? null) {
                            $indicators['transaction_date_to'] = 'Transaction date to: ' . Carbon::parse($data['transaction_date_to'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_remarks')
                    ->label('Has Remarks')
                    ->placeholder('All records')
                    ->trueLabel('With remarks')
                    ->falseLabel('Without remarks')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('remarks'),
                        false: fn(Builder $query) => $query->whereNull('remarks'),
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('addQuantity')
                        ->label('Add Quantity')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('inventory_id')
                                ->relationship('inventory', 'item_name')
                                ->required(),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->minValue(1),
                            Forms\Components\Textarea::make('remarks')
                                ->maxLength(65535),
                            Forms\Components\DatePicker::make('transaction_date')
                                ->required()
                                ->default(now()),
                        ])
                        ->action(function (array $data, $record): void {
                            $inventory = Inventory::findOrFail($data['inventory_id']);
                            $quantityBefore = $inventory->quantity;
                            $quantityAfter = $quantityBefore + $data['quantity'];

                            $inventory->update(['quantity' => $quantityAfter]);

                            InventoryTracking::create([
                                'inventory_id' => $data['inventory_id'],
                                'quantity_before' => $quantityBefore,
                                'quantity_after' => $quantityAfter,
                                'transaction_type' => 'addition',
                                'remarks' => $data['remarks'],
                                'transaction_date' => $data['transaction_date'],
                            ]);

                            Notification::make()
                                ->title('Quantity added successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('deductQuantity')
                        ->label('Deduct Quantity')
                        ->icon('heroicon-o-minus-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Select::make('inventory_id')
                                ->relationship('inventory', 'item_name')
                                ->required(),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->minValue(1),
                            Forms\Components\Textarea::make('remarks')
                                ->maxLength(65535),
                            Forms\Components\DatePicker::make('transaction_date')
                                ->required()
                                ->default(now()),
                        ])
                        ->action(function (array $data, $record): void {
                            $inventory = Inventory::findOrFail($data['inventory_id']);
                            $quantityBefore = $inventory->quantity;
                            $quantityAfter = $quantityBefore - $data['quantity'];

                            if ($quantityAfter < 0) {
                                Notification::make()
                                    ->title('Insufficient quantity')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $inventory->update(['quantity' => $quantityAfter]);

                            InventoryTracking::create([
                                'inventory_id' => $data['inventory_id'],
                                'quantity_before' => $quantityBefore,
                                'quantity_after' => $quantityAfter,
                                'transaction_type' => 'deduction',
                                'remarks' => $data['remarks'],
                                'transaction_date' => $data['transaction_date'],
                            ]);

                            Notification::make()
                                ->title('Quantity deducted successfully')
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(InventoryTrackingExporter::class)
                        ->color('success')
                        ->icon('heroicon-o-document-arrow-down')
                ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(InventoryTrackingExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-up-tray'),
                ImportAction::make()
                    ->importer(InventoryTrackingImporter::class)
                    ->color('primary')
                    ->icon('heroicon-o-arrow-down-tray'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryTrackings::route('/'),
            'create' => Pages\CreateInventoryTracking::route('/create'),
            'edit' => Pages\EditInventoryTracking::route('/{record}/edit'),
        ];
    }
}
