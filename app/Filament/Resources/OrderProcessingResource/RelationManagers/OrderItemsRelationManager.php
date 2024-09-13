<?php

namespace App\Filament\Resources\OrderProcessingResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter product name')
                    ->autocomplete('off')
                    ->autofocus()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $state, Forms\Set $set) {
                        $set('product_name', ucwords($state));
                    }),

                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1)
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

                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Created At')
                    ->displayFormat('d/m/Y H:i')
                    ->disabled(),

                Forms\Components\DateTimePicker::make('updated_at')
                    ->label('Last Updated At')
                    ->displayFormat('d/m/Y H:i')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->label('Total'),
                    ]),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('idr')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('idr')
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->label('Total'),
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true)
                    ->tooltip(fn(Model $record): string => $record->created_at->diffForHumans()),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true)
                    ->tooltip(fn(Model $record): string => $record->updated_at->diffForHumans()),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
