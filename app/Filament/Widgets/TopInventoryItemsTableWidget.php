<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use App\Models\ManagementStock\Inventory;
use Filament\Widgets\TableWidget as BaseWidget;

class TopInventoryItemsTableWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Inventory::query()
                    ->select('inventories.*', DB::raw('COUNT(inventory_trackings.id) as movement_count'))
                    ->leftJoin('inventory_trackings', 'inventories.id', '=', 'inventory_trackings.inventory_id')
                    ->groupBy('inventories.id')
                    ->orderByDesc('movement_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Current Stock')
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_count')
                    ->label('Movement Count')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'in_stock' => 'success',
                        'out_of_stock' => 'danger',
                        'discontinued' => 'gray',
                    })
            ])
            ->defaultSort('movement_count', 'desc');
    }
}
