<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ManagementStock\Supplier;
use Filament\Widgets\TableWidget as BaseWidget;

class SupplierTableWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Supplier::query()
                    ->withSum('supplierTransactions', 'amount')
                    ->orderByDesc('supplier_transactions_sum_amount')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Supplier Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_code')
                    ->label('Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('supplierTransactions_sum_amount')
                    ->label('Total Transaction Value')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                    }),
            ])
            ->defaultSort('supplierTransactions_sum_amount', 'desc');
    }
}
