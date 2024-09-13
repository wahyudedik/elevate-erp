<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Exports\PurchaseTransactionExporter;
use App\Filament\Imports\PurchaseTransactionImporter;
use App\Filament\Resources\PurchaseTransactionResource\Pages;
use App\Models\ManagementSalesAndPurchasing\PurchaseTransaction;
use App\Filament\Resources\PurchaseTransactionResource\RelationManagers;
use App\Filament\Resources\PurchaseTransactionResource\RelationManagers\SupplierRelationManager;
use App\Filament\Resources\PurchaseTransactionResource\RelationManagers\PurchaseItemsRelationManager;
use Filament\Tables\Actions\ExportBulkAction;

class PurchaseTransactionResource extends Resource
{
    protected static ?string $model = PurchaseTransaction::class;

    protected static ?string $navigationBadgeTooltip = 'Total Purchase Transactions';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Sales And Purchasing';

    protected static ?string $navigationParentItem = null;

    protected static ?string $navigationIcon = 'bx-purchase-tag-alt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Transaction Details')
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'supplier_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('supplier_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('supplier_code')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('contact_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('fax')
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('website')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('tax_identification_number')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('address')
                                    ->required()
                                    ->maxLength(65535),
                                Forms\Components\TextInput::make('city')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('state')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('postal_code')
                                    ->required()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('country')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                    ]),
                                Forms\Components\TextInput::make('credit_limit')
                                    ->numeric()
                                    ->required(),
                            ]),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(999999999999999.99)
                            ->step(0.01),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'received' => 'Received',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\Select::make('purchasing_agent_id')
                            ->relationship('purchasingAgent', 'first_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn(?PurchaseTransaction $record): string => $record ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last Modified At')
                            ->content(fn(?PurchaseTransaction $record): string => $record ? $record->updated_at->diffForHumans() : '-'),
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
                Tables\Columns\TextColumn::make('supplier.supplier_name')
                    ->label('Supplier')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'pending',
                        'success' => 'received',
                    ]),
                Tables\Columns\TextColumn::make('purchasingAgent.first_name')
                    ->label('Purchasing Agent')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'supplier_name')
                    ->searchable()
                    ->preload()
                    ->label('Supplier'),
                Tables\Filters\Filter::make('transaction_date')
                    ->label('Transaction Date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From Date'),
                        Forms\Components\DatePicker::make('to')->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    })->columns(2),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'received' => 'Received',
                        'cancelled' => 'Cancelled',
                    ])
                    ->label('Status'),
                Tables\Filters\SelectFilter::make('purchasing_agent')
                    ->relationship('purchasingAgent', 'first_name')
                    ->searchable()
                    ->preload()
                    ->label('Purchasing Agent'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('change_status')
                    ->label('Change Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'received' => 'Received',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                    ])
                    ->action(function (PurchaseTransaction $record, array $data) {
                        $record->update(['status' => $data['status']]);
                        Notification::make()
                            ->title('Status updated successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('print_invoice')
                    ->label('Print Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    // ->url(fn(PurchaseTransaction $record) => route('purchase-transaction.print-invoice', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('send_to_supplier')
                        ->label('Send to Supplier')
                        ->icon('heroicon-o-paper-airplane')
                        ->requiresConfirmation()
                        ->action(function (PurchaseTransaction $record) {
                            // Logic to send to supplier
                            Notification::make()
                                ->title('Sent to supplier successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('mark_as_paid')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-banknotes')
                        ->requiresConfirmation()
                        ->action(function (PurchaseTransaction $record) {
                            // Logic to mark as paid
                            Notification::make()
                                ->title('Marked as paid successfully')
                                ->success()
                                ->send();
                        }),
                ])->label('More Actions')->icon('heroicon-m-ellipsis-vertical'),

            ])
            ->headerActions([
                ExportAction::make()->exporter(PurchaseTransactionExporter::class),
                ImportAction::make()->importer(PurchaseTransactionImporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

                ExportBulkAction::make()->exporter(PurchaseTransactionExporter::class),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PurchaseItemsRelationManager::class,
            SupplierRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseTransactions::route('/'),
            'create' => Pages\CreatePurchaseTransaction::route('/create'),
            'edit' => Pages\EditPurchaseTransaction::route('/{record}/edit'),
        ];
    }
}
