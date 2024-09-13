<?php

namespace App\Filament\Resources;

use App\Filament\Exports\SaleItemExporter;
use App\Filament\Imports\SaleItemImporter;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ManagementCRM\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\SaleItemResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SaleItemResource\RelationManagers;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;

class SaleItemResource extends Resource
{
    protected static ?string $model = SaleItem::class;

    protected static ?string $navigationBadgeTooltip = 'Total Sale Items';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management CRM';

    protected static ?string $navigationParentItem = 'Sales';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sale_id')
                    ->relationship('sale', 'id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Sale'),
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
                    ->maxValue(42949672.95)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        function (string $state, Forms\Set $set, string $operation, $get) {
                            $set('total_price', $get('quantity') * $get('unit_price'));
                        }
                    )
                    ->reactive(),
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('IDR')
                    ->maxValue(4294979799672.95)
                    ->default('null')
                    ->reactive(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sale.id')
                    ->label('Sale ID')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product Name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->toggleable()
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->toggleable()
                    ->money('IDR')
                    ->sortable(),
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
                Tables\Filters\Filter::make('unit_price')
                    ->form([
                        Forms\Components\TextInput::make('unit_price_from')
                            ->label('From')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('unit_price_to')
                            ->label('To')
                            ->numeric()
                            ->prefix('IDR'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['unit_price_from'],
                                fn(Builder $query, $price): Builder => $query->where('unit_price', '>=', $price),
                            )
                            ->when(
                                $data['unit_price_to'],
                                fn(Builder $query, $price): Builder => $query->where('unit_price', '<=', $price),
                            );
                    }),
                Tables\Filters\Filter::make('total_price')
                    ->form([
                        Forms\Components\TextInput::make('total_price_from')
                            ->label('From')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('total_price_to')
                            ->label('To')
                            ->numeric()
                            ->prefix('IDR'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['total_price_from'],
                                fn(Builder $query, $price): Builder => $query->where('total_price', '>=', $price),
                            )
                            ->when(
                                $data['total_price_to'],
                                fn(Builder $query, $price): Builder => $query->where('total_price', '<=', $price),
                            );
                    })->columns(2),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
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
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('updateQuantity')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->label('New Quantity'),
                        ])
                        ->action(function (SaleItem $record, array $data) {
                            $record->update([
                                'quantity' => $data['quantity'],
                                'total_price' => $data['quantity'] * $record->unit_price,
                            ]);
                        })
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('updateUnitPrice')
                        ->form([
                            Forms\Components\TextInput::make('unit_price')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->prefix('IDR')
                                ->label('New Unit Price'),
                        ])
                        ->action(function (SaleItem $record, array $data) {
                            $record->update([
                                'unit_price' => $data['unit_price'],
                                'total_price' => $record->quantity * $data['unit_price'],
                            ]);
                        })
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->requiresConfirmation(),
                ])
            ])
            ->headerActions([
                ExportAction::make()->exporter(SaleItemExporter::class),
                ImportAction::make()->importer(SaleItemImporter::class)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateBulkQuantity')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->required()
                                ->numeric()
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
                        })
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->requiresConfirmation(),
                ]),
                ExportBulkAction::make()->exporter(SaleItemExporter::class)
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
            'index' => Pages\ListSaleItems::route('/'),
            'create' => Pages\CreateSaleItem::route('/create'),
            'edit' => Pages\EditSaleItem::route('/{record}/edit'),
        ];
    }
}
