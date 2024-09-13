<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\Facades\Redirect;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\ManagementStock\SupplierTransactions;
use App\Filament\Exports\SupplierTransactionsExporter;
use App\Filament\Imports\SupplierTransactionsImporter;
use App\Filament\Resources\SupplierTransactionsResource\Pages;
use App\Filament\Resources\SupplierTransactionsResource\RelationManagers;

class SupplierTransactionsResource extends Resource
{
    protected static ?string $model = SupplierTransactions::class;

    protected static ?string $navigationBadgeTooltip = 'Total Suppliers';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Stock';

    protected static ?string $navigationParentItem = 'Suppliers';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('supplier_id')
                    ->relationship('supplier', 'supplier_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('transaction_code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('Enter transaction code')
                    ->maxLength(255),
                Forms\Components\Select::make('transaction_type')
                    ->options([
                        'purchase_order' => 'Purchase Order',
                        'payment' => 'Payment',
                        'refund' => 'Refund',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->maxValue(999999999999999.99),
                Forms\Components\DatePicker::make('transaction_date')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->placeholder('Enter additional notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.supplier_name')
                    ->label('Supplier')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_code')
                    ->label('Transaction Code')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->label('Transaction Type')
                    ->colors([
                        'primary' => 'purchase_order',
                        'success' => 'payment',
                        'danger' => 'refund',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->toggleable()
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Transaction Date')
                    ->date()
                    ->default(now())
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->toggleable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
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
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'supplier_name')
                    ->searchable()
                    ->preload()
                    ->label('Supplier'),
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->options([
                        'purchase_order' => 'Purchase Order',
                        'payment' => 'Payment',
                        'refund' => 'Refund',
                    ])
                    ->label('Transaction Type'),
                Tables\Filters\Filter::make('amount')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->numeric()
                            ->label('From'),
                        Forms\Components\TextInput::make('amount_to')
                            ->numeric()
                            ->label('To'),
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
                            $indicators['amount_from'] = 'Amount from: ' . $data['amount_from'];
                        }
                        if ($data['amount_to'] ?? null) {
                            $indicators['amount_to'] = 'Amount to: ' . $data['amount_to'];
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_notes')
                    ->label('Has Notes')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('notes'),
                        false: fn(Builder $query) => $query->whereNull('notes'),
                    )
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('printInvoice')
                        ->label('Print Invoice')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn(SupplierTransactions $record): string => route('supplier-transactions.print', $record))
                        ->openUrlInNewTab(),
                ])
            ])
            ->headerActions([
                ExportAction::make()->exporter(SupplierTransactionsExporter::class),
                ImportAction::make()->importer(SupplierTransactionsImporter::class)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()->exporter(SupplierTransactionsExporter::class)
            ])
            ->emptyStateActions([
                CreateAction::make()
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
            'index' => Pages\ListSupplierTransactions::route('/'),
            'create' => Pages\CreateSupplierTransactions::route('/create'),
            'edit' => Pages\EditSupplierTransactions::route('/{record}/edit'),
        ];
    }
}
