<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeStatementResource\Pages;
use App\Filament\Resources\IncomeStatementResource\RelationManagers;
use App\Models\ManagementFinancial\IncomeStatement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource; 
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IncomeStatementResource extends Resource
{
    protected static ?string $model = IncomeStatement::class;

    protected static ?string $navigationBadgeTooltip = 'Total Income Statements';

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
            'index' => Pages\ListIncomeStatements::route('/'),
            'create' => Pages\CreateIncomeStatement::route('/create'),
            'edit' => Pages\EditIncomeStatement::route('/{record}/edit'),
        ];
    }
}
