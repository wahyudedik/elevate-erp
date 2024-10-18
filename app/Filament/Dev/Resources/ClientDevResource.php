<?php

namespace App\Filament\Dev\Resources;

use App\Filament\Dev\Resources\ClientDevResource\Pages;
use App\Filament\Dev\Resources\ClientDevResource\RelationManagers;
use App\Models\ClientDev;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientDevResource extends Resource
{
    protected static ?string $model = ClientDev::class;

    protected static ?string $navigationGroup = 'Client Dev';

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageClientDevs::route('/'),
        ];
    }
}
