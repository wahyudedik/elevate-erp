<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementFinancial\BalanceSheet;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BalanceSheetResource\Pages;
use App\Filament\Resources\BalanceSheetResource\RelationManagers;

class BalanceSheetResource extends Resource
{
    protected static ?string $model = BalanceSheet::class;

    protected static ?string $navigationBadgeTooltip = 'Total Balance Sheets';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Financial';

    protected static ?string $navigationParentItem = 'Financial Reporting';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListBalanceSheets::route('/'),
            'create' => Pages\CreateBalanceSheet::route('/create'),
            'edit' => Pages\EditBalanceSheet::route('/{record}/edit'),
        ];
    }
}
