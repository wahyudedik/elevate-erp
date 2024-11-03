<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ManagementCRM\OrderItem;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\OrderItemExporter;
use App\Filament\Imports\OrderItemImporter;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\OrderItemResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderItemResource\RelationManagers;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static ?string $navigationBadgeTooltip = 'Total Order Items';

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
                Forms\Components\Section::make('Order Item Details')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->relationship('orderProcessing', 'id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Order'),
                        Forms\Components\TextInput::make('product_name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter product name')
                            ->autocomplete('off')
                            ->autofocus(),

                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->step(1)
                            ->default(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set, $get) => $set('total_price', $state * $get('unit_price'))),

                        Forms\Components\TextInput::make('unit_price')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->minValue(0.01)
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set, $get) => $set('total_price', $state * $get('quantity'))),

                        Forms\Components\TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->disabled()
                            ->dehydrated()
                            ->afterStateHydrated(fn($component, $state) => $component->state(number_format($state, 2))),

                    ])
                    ->columns(2),
                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Updated At')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->toggledHiddenByDefault()
                    ->copyMessage('Order ID copied to clipboard')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product Name')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money('IDR')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->money('IDR')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->money('usd'),
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_id')
                    ->relationship('orderProcessing', 'id')
                    ->label('Order ID')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
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
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('updateQuantity')
                        ->icon('heroicon-o-plus')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->label('New Quantity'),
                        ])
                        ->action(function (OrderItem $record, array $data) {
                            $record->update([
                                'quantity' => $data['quantity'],
                                'total_price' => $data['quantity'] * $record->unit_price,
                            ]);
                            Notification::make()
                                ->title('Quantity Updated')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('updatePrice')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('unit_price')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->label('New Unit Price'),
                        ])
                        ->action(function (OrderItem $record, array $data) {
                            $record->update([
                                'unit_price' => $data['unit_price'],
                                'total_price' => $record->quantity * $data['unit_price'],
                            ]);
                            Notification::make()
                                ->title('Price Updated')
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->headerActions([
                ExportAction::make()->exporter(OrderItemExporter::class),
                ImportAction::make()->importer(OrderItemImporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateBulkQuantity')
                        ->icon('heroicon-o-plus')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->label('New Quantity'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'quantity' => $data['quantity'],
                                    'total_price' => $data['quantity'] * $record->unit_price,
                                ]);
                            });
                            Notification::make()
                                ->title('Quantities Updated')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('updateBulkPrice')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('unit_price')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->label('New Unit Price'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'unit_price' => $data['unit_price'],
                                    'total_price' => $record->quantity * $data['unit_price'],
                                ]);
                            });
                            Notification::make()
                                ->title('Prices Updated')
                                ->success()
                                ->send();
                        }),
                ]),
                ExportBulkAction::make()->exporter(OrderItemExporter::class),
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
            'index' => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}
