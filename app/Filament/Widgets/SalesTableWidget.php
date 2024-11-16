<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ManagementCRM\SaleItem;
use Filament\Widgets\TableWidget as BaseWidget;

class SalesTableWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SaleItem::query()
                    ->selectRaw('product_name, SUM(quantity) as total_quantity, SUM(total_price) as total_revenue')
                    ->groupBy('product_name')
                    ->orderByDesc('total_quantity')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Units Sold')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->heading('Top Selling Products');
    }
}
