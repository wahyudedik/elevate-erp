<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\ManagementStock\SupplierTransactions;

class SupplierPendingPaymentTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Pending Supplier Payments';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SupplierTransactions::query()
                    ->where('transaction_type', 'payment')
                    ->whereNull('payment_date')
                    ->latest()
            )
            ->columns([
                TextColumn::make('supplier.supplier_name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('transaction_code')
                    ->label('Transaction Code')
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->color(
                        fn($record) =>
                        $record->due_date < now() ? 'danger' : 'warning'
                    ),
            ])
            ->defaultSort('due_date', 'asc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->paginated([5, 10, 25, 50]);
    }
}
