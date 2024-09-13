<?php

namespace App\Filament\Resources;

use App\Filament\Exports\SalesItemExporter;
use App\Filament\Imports\SalesItemImporter;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\SalesItemResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\ManagementSalesAndPurchasing\SalesItem;
use App\Filament\Resources\SalesItemResource\RelationManagers;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;

class SalesItemResource extends Resource
{
    protected static ?string $model = SalesItem::class;

    protected static ?string $navigationBadgeTooltip = 'Total Sales Transactions';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Sales And Purchasing';

    protected static ?string $navigationParentItem = 'Sales Transactions';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sales_transaction_id')
                    ->relationship('salesTransaction', 'id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Sales Transaction'),

                Forms\Components\TextInput::make('product_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
                Forms\Components\TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->prefix('IDR')
                    ->maxValue(42949672.95),
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
                            ->content(fn(?SalesItem $record): string => $record ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last Modified At')
                            ->content(fn(?SalesItem $record): string => $record ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ])
            ->columns(2)
            ->reactive()
            ->live();
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
                Tables\Columns\TextColumn::make('sales_transaction_id')
                    ->label('Sales Transaction ID')
                    ->sortable()
                    ->toggleable()
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
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),

            ])
            ->filters([
                SelectFilter::make('sales_transaction_id')
                    ->relationship('salesTransaction', 'id')
                    ->label('Sales Transaction')
                    ->searchable()
                    ->preload(),
                Filter::make('quantity')
                    ->form([
                        TextInput::make('quantity_from')
                            ->label('Minimum Quantity')
                            ->numeric()
                            ->placeholder('From')
                            ->autocomplete('off'),
                        TextInput::make('quantity_to')
                            ->label('Maximum Quantity')
                            ->numeric()
                            ->placeholder('To')
                            ->autocomplete('off'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['quantity_from'],
                                fn(Builder $query, $value): Builder => $query->where('quantity', '>=', $value)
                            )
                            ->when(
                                $data['quantity_to'],
                                fn(Builder $query, $value): Builder => $query->where('quantity', '<=', $value)
                            );
                    })->columns(2),

                Filter::make('unit_price')
                    ->form([
                        TextInput::make('unit_price_from')
                            ->label('Min Unit Price')
                            ->numeric()
                            ->placeholder('From')
                            ->autocomplete('off'),
                        TextInput::make('unit_price_to')
                            ->label('Max Unit Price')
                            ->numeric()
                            ->placeholder('To')
                            ->autocomplete('off'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['unit_price_from'],
                                fn(Builder $query, $value): Builder => $query->where('unit_price', '>=', $value)
                            )
                            ->when(
                                $data['unit_price_to'],
                                fn(Builder $query, $value): Builder => $query->where('unit_price', '<=', $value)
                            );
                    })->columns(2),

                Filter::make('total_price')
                    ->form([
                        TextInput::make('total_price_from')
                            ->label('Minimum Total Price')
                            ->numeric()
                            ->placeholder('From')
                            ->autocomplete('off'),
                        TextInput::make('total_price_to')
                            ->label('Maximum Total Price')
                            ->numeric()
                            ->placeholder('To')
                            ->autocomplete('off'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['total_price_from'],
                                fn(Builder $query, $value): Builder => $query->where('total_price', '>=', $value)
                            )
                            ->when(
                                $data['total_price_to'],
                                fn(Builder $query, $value): Builder => $query->where('total_price', '<=', $value)
                            );
                    })->columns(2),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Created From')
                            ->placeholder('From')
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('created_until')
                            ->label('Created Until')
                            ->placeholder('Until')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    })->columns(2),

                Filter::make('updated_at')
                    ->form([
                        DatePicker::make('updated_from')
                            ->label('Updated From')
                            ->placeholder('From')
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('updated_until')
                            ->label('Updated Until')
                            ->placeholder('Until')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['updated_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $date)
                            )
                            ->when(
                                $data['updated_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('updated_at', '<=', $date)
                            );
                    })->columns(2),
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
                                ->label('New Unit Price')
                                ->required()
                                ->numeric()
                                ->prefix('IDR'),
                        ])
                        ->action(function (SalesItem $record, array $data): void {
                            $record->update([
                                'unit_price' => $data['unit_price'],
                                'total_price' => $data['unit_price'] * $record->quantity,
                            ]);
                            Notification::make()
                                ->title('Price updated successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('adjustQuantity')
                        ->label('Adjust Quantity')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->label('New Quantity')
                                ->required()
                                ->numeric()
                                ->minValue(1),
                        ])
                        ->action(function (SalesItem $record, array $data): void {
                            $record->update([
                                'quantity' => $data['quantity'],
                                'total_price' => $record->unit_price * $data['quantity'],
                            ]);
                            Notification::make()
                                ->title('Quantity adjusted successfully')
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updatePrices')
                        ->label('Update Prices')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('percentage')
                                ->label('Percentage Increase/Decrease')
                                ->required()
                                ->numeric()
                                ->suffix('%')
                                ->helperText('Use positive values for increase, negative for decrease.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (SalesItem $record) use ($data) {
                                $newPrice = $record->unit_price * (1 + $data['percentage'] / 100);
                                $record->update([
                                    'unit_price' => $newPrice,
                                    'total_price' => $newPrice * $record->quantity,
                                ]);
                            });
                            Notification::make()
                                ->title('Prices updated successfully')
                                ->success()
                                ->send();
                        }),
                    ExportBulkAction::make()->exporter(SalesItemExporter::class)
                ]),
            ])
            ->headerActions([
                ExportAction::make()->exporter(SalesItemExporter::class),
                ImportAction::make()->importer(SalesItemImporter::class)
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
            'index' => Pages\ListSalesItems::route('/'),
            'create' => Pages\CreateSalesItem::route('/create'),
            'edit' => Pages\EditSalesItem::route('/{record}/edit'),
        ];
    }
}
