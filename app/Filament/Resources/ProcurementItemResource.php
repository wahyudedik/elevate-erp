<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementStock\ProcurementItem;
use App\Filament\Exports\ProcurementItemExporter;
use App\Filament\Imports\ProcurementItemImporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProcurementItemResource\Pages;
use App\Filament\Resources\ProcurementItemResource\RelationManagers;

class ProcurementItemResource extends Resource
{
    protected static ?string $model = ProcurementItem::class;

    protected static ?string $navigationBadgeTooltip = 'Total Procurement';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Stock';

    protected static ?string $navigationParentItem = 'Procurements';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Procurement Item Details')
                    ->schema([
                        Forms\Components\Select::make('procurement_id')
                            ->relationship('procurement', 'id')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('item_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->suffix('Units')
                            ->step(1)
                            ->default(1)
                            ->live()
                            ->afterStateUpdated(fn($state, callable $set, $get) => $set('total_price', $state * $get('unit_price'))),
                        Forms\Components\TextInput::make('unit_price')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->minValue(0.01)
                            ->step(0.01)
                            ->live()
                            ->afterStateUpdated(fn($state, callable $set, $get) => $set('total_price', $state * $get('quantity'))),
                        Forms\Components\TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->disabled()
                            ->dehydrated()
                            ->afterStateHydrated(fn($component, $state) => $component->state(number_format($state, 2))),
                    ])
                    ->columns(2)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('procurement.id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('item_name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('procurement')
                    ->relationship('procurement', 'id')
                    ->label('Procurement')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->label('Created At')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
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
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('calculate_total')
                    ->label('Calculate Total')
                    ->icon('heroicon-o-calculator')
                    ->action(function (ProcurementItem $record) {
                        $record->total_price = $record->quantity * $record->unit_price;
                        $record->save();
                    })
                    ->requiresConfirmation()
                    ->color('success'),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (ProcurementItem $record) {
                        $newRecord = $record->replicate();
                        $newRecord->save();
                    })
                    ->requiresConfirmation()
                    ->color('warning'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('calculate_totals')
                        ->label('Calculate Totals')
                        ->icon('heroicon-o-calculator')
                        ->action(function (Collection $records) {
                            $records->each(function (ProcurementItem $record) {
                                $record->total_price = $record->quantity * $record->unit_price;
                                $record->save();
                            });
                        })
                        ->requiresConfirmation()
                        ->color('success'),
                ]),
                ExportBulkAction::make()->exporter(ProcurementItemExporter::class),
            ])
            ->headerActions([
                ExportAction::make()->exporter(ProcurementItemExporter::class),
                ImportAction::make()->importer(ProcurementItemImporter::class),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Procurement Item')
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
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
            'index' => Pages\ListProcurementItems::route('/'),
            'create' => Pages\CreateProcurementItem::route('/create'),
            'edit' => Pages\EditProcurementItem::route('/{record}/edit'),
        ];
    }
}
