<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Models\ManagementStock\Inventory;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementStock\InventoryTracking;
use App\Filament\Exports\InventoryTrackingExporter;
use App\Filament\Imports\InventoryTrackingImporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InventoryTrackingResource\Pages;
use App\Filament\Resources\InventoryTrackingResource\RelationManagers;
use App\Filament\Resources\InventoryTrackingResource\RelationManagers\InventoryRelationManager;

class InventoryTrackingResource extends Resource
{
    protected static ?string $model = InventoryTracking::class;

    protected static ?string $navigationLabel = 'Tracking Inventory';

    protected static ?string $modelLabel = 'Tracking Inventory';
    
    protected static ?string $pluralModelLabel = 'Tracking Inventory';

    protected static ?int $navigationSort = 28;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'inventoryTracking';

    protected static ?string $navigationGroup = 'Management Stock';

    protected static ?string $navigationIcon = 'hugeicons-shipment-tracking';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Inventory Tracking Details')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('inventory_id')
                            ->relationship('inventory', 'item_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\Section::make('Item Details')
                                    ->schema([
                                        Forms\Components\Hidden::make('company_id')
                                            ->default(Filament::getTenant()->id),
                                        Forms\Components\Select::make('branch_id')
                                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                                            ->nullable()
                                            ->searchable()
                                            ->preload(),
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
                                    ])
                                    ->columns(2),
                                Forms\Components\Section::make('Item Location')
                                    ->schema([
                                        Forms\Components\TextInput::make('location')
                                            ->maxLength(255),
                                        Forms\Components\Select::make('supplier_id')
                                            ->relationship('supplier', 'supplier_name', fn($query) => $query->where('status', 'active'))
                                            ->required()
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\Select::make('status')
                                            ->options([
                                                'in_stock' => 'In Stock',
                                                'out_of_stock' => 'Out of Stock',
                                                'discontinued' => 'Discontinued',
                                            ])
                                            ->required()
                                            ->default('in_stock'),
                                    ])
                                    ->columns(2),
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
                        Forms\Components\RichEditor::make('remarks')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->default(now())
                            ->required(),
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
            ])
            ->columns(2);
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
                    ->html()
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
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
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
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
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
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    ExportBulkAction::make()->exporter(InventoryTrackingExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export inventory tracking completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()->exporter(InventoryTrackingExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export inventory tracking completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(InventoryTrackingImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Import inventory tracking completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->icon('heroicon-o-cog-6-tooth')
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InventoryRelationManager::class,
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
            'inventory_id',
            'quantity_before',
            'quantity_after',
            'transaction_type',
            'remarks',
            'transaction_date',
        ];
    }
}
