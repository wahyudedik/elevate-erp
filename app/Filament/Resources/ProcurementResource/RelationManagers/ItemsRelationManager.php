<?php

namespace App\Filament\Resources\ProcurementResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item_name')
                    ->required()
                    ->maxLength(255)
                    ->label('Item Name')
                    ->placeholder('Enter item name')
                    ->autocomplete('off')
                    ->autofocus()
                    ->columnSpan(2),

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

                Forms\Components\Hidden::make('procurement_id')
                    ->default(fn($livewire) => $livewire->ownerRecord->id),
            ])->live(onBlur: true);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_name')
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap()
                    ->copyable()
                    ->tooltip('Item Name')
                    ->extraAttributes(['class' => 'font-medium'])
                    ->icon('heroicon-o-shopping-bag')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('quantity')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->alignRight()
                    ->icon('heroicon-o-calculator')
                    ->color('success'),

                Tables\Columns\TextColumn::make('unit_price')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->money('IDR')
                    ->alignRight()
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('total_price')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->money('IDR')
                    ->alignRight()
                    ->icon('heroicon-o-banknotes')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-clock'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-arrow-path'),
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
