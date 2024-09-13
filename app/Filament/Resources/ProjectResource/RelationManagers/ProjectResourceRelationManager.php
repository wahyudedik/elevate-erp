<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResourceRelationManager extends RelationManager
{
    protected static string $relationship = 'projectResource';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('resource_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('resource_type')
                    ->required()
                    ->options([
                        'human' => 'Human',
                        'material' => 'Material',
                        'financial' => 'Financial',
                    ]),
                Forms\Components\TextInput::make('resource_cost')
                    ->numeric()
                    ->prefix('IDR')
                    ->maxValue(42949672.95)
                    ->step(0.01),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('resource_name')
            ->columns([
                Tables\Columns\TextColumn::make('resource_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resource_type')
                    ->badge()
                    ->colors([
                        'primary' => 'human',
                        'success' => 'material',
                        'warning' => 'financial',
                    ]),
                Tables\Columns\TextColumn::make('resource_cost')
                    ->money('idr')
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
