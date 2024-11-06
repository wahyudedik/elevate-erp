<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Clusters\Procurement;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementStock\PurchaseItem;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\PurchaseItemExporter;
use App\Filament\Imports\PurchaseItemImporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PurchaseItemResource\Pages;
use App\Filament\Resources\PurchaseItemResource\RelationManagers;
use App\Filament\Resources\PurchaseItemResource\RelationManagers\PurchaseTransactionRelationManager;

class PurchaseItemResource extends Resource
{
    protected static ?string $model = PurchaseItem::class;

    protected static ?string $cluster = Procurement::class;

    protected static ?int $navigationSort = 25;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'purchaseItems';

    protected static ?string $navigationGroup = 'Purchases';

    protected static ?string $navigationIcon = 'iconsax-two-forward-item';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Item Details')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('purchase_transaction_id')
                            ->relationship('purchaseTransaction', 'id', fn($query) => $query->where('status', 'pending'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select Purchase Transaction'),

                        Forms\Components\TextInput::make('product_name')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete()
                            ->placeholder('Enter product name'),

                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->step(1)
                            ->placeholder('Enter quantity'),

                        Forms\Components\TextInput::make('unit_price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('IDR')
                            ->placeholder('Enter unit price'),

                        Forms\Components\Placeholder::make('total_price')
                            ->default(function ($get) {
                                return $get('quantity') * $get('unit_price');
                            })
                            ->content(function ($get) {
                                return 'IDR ' . number_format($get('quantity') * $get('unit_price'), 2);
                            }),
                    ])->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn(?PurchaseItem $record): string => $record ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last Modified At')
                            ->content(fn(?PurchaseItem $record): string => $record ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ])->live()->reactive();
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
                Tables\Columns\TextColumn::make('purchaseTransaction.id')
                    ->label('Purchase Transaction')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
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
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
                Tables\Filters\SelectFilter::make('purchase_transaction_id')
                    ->relationship('purchaseTransaction', 'id')
                    ->label('Purchase Transaction')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_purchase_transaction')
                    ->label('Has Purchase Transaction')
                    ->placeholder('All items')
                    ->trueLabel('With Purchase Transaction')
                    ->falseLabel('Without Purchase Transaction')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('purchaseTransaction'),
                        false: fn(Builder $query) => $query->whereDoesntHave('purchaseTransaction'),
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('updatePrice')
                        ->label('Update Price')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->required()
                                ->numeric()
                                ->prefix('$'),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Quantity')
                                ->required()
                                ->numeric()
                                ->minValue(1),
                        ])
                        ->action(function (PurchaseItem $record, array $data): void {
                            $record->update([
                                'unit_price' => $data['unit_price'],
                                'quantity' => $data['quantity'],
                                'total_price' => $data['unit_price'] * $data['quantity'],
                            ]);
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Price updated')
                                ->body('The purchase item price has been updated successfully.')
                        ),
                    Tables\Actions\Action::make('attachTransaction')
                        ->label('Attach Transaction')
                        ->icon('heroicon-o-link')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('purchase_transaction_id')
                                ->label('Purchase Transaction')
                                ->relationship('purchaseTransaction', 'id')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (PurchaseItem $record, array $data): void {
                            $record->update([
                                'purchase_transaction_id' => $data['purchase_transaction_id'],
                            ]);
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Transaction attached')
                                ->body('The purchase item has been attached to a transaction successfully.')
                        ),

                ])
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()->exporter(PurchaseItemExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export purchase item completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(PurchaseItemImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Import purchase item completed' . ' ' . now())
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
                    ExportBulkAction::make()->exporter(PurchaseItemExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export purchase item completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PurchaseTransactionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseItems::route('/'),
            'create' => Pages\CreatePurchaseItem::route('/create'),
            'edit' => Pages\EditPurchaseItem::route('/{record}/edit'),
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
            'purchase_transaction_id',
            'product_name',
            'quantity',
            'unit_price',
            'total_price',
        ];
    }
}
