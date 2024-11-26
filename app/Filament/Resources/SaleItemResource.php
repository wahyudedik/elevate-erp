<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Sales;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagementCRM\SaleItem;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\SaleItemExporter;
use App\Filament\Imports\SaleItemImporter;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\SaleItemResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SaleItemResource\RelationManagers;
use App\Filament\Resources\SaleItemResource\RelationManagers\SaleRelationManager;
use Filament\Actions\CreateAction as ActionsCreateAction;
use Filament\Navigation\MenuItem;

class SaleItemResource extends Resource
{
    protected static ?string $model = SaleItem::class;

    protected static ?string $navigationLabel = 'Penjualan Barang';

    protected static ?string $modelLabel = 'Penjualan Barang';
    
    protected static ?string $pluralModelLabel = 'Penjualan Barang';

    protected static ?string $cluster = Sales::class;

    protected static ?int $navigationSort = 20;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'saleItem';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationIcon = 'iconsax-two-receipt-item';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Items Sales')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('sale_id')
                            ->relationship('sale', 'id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Sale ID'),
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
                    ])->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn($record): string => $record?->updated_at ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable(),
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
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
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
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
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
                CreateAction::make()->label('Create Sale')->url('sales/create')->icon(('heroicon-o-plus'),),
                ActionGroup::make([
                    ExportAction::make()->exporter(SaleItemExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export Item Sale completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(SaleItemImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Import Item Sale completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->icon('heroicon-o-cog-6-tooth')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
                    ExportBulkAction::make()->exporter(SaleItemExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export Item Sale completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SaleRelationManager::class,
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'company_id',
            'branch_id',
            'sale_id',
            'product_name',
            'quantity',
            'unit_price',  // Harga per unit
            'total_price',  // quantity * price
        ];
    }
}
