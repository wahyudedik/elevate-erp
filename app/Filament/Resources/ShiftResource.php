<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ManagementSDM\Shift;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ShiftResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ShiftResource\RelationManagers;
use App\Filament\Resources\ShiftResource\RelationManagers\ScheduleRelationManager;
use Filament\Tables\Actions\ActionGroup;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationBadgeTooltip = 'Total Shift';

    protected static ?string $navigationLabel = 'Shift';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'shift';

    protected static ?string $navigationGroup = 'Management SDM';

    protected static ?string $navigationParentItem = 'Attendance';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Shift Details')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->nullable()
                            ->preload()
                            ->label('Branch')
                            ->searchable(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Shift Name'),
                        Forms\Components\TimePicker::make('start_time')
                            ->required()
                            ->label('Start Time'),
                        Forms\Components\TimePicker::make('end_time')
                            ->required()
                            ->label('End Time'),
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
            ])
            ->columns(3);
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Shift Name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('End Time')
                    ->icon('heroicon-o-clock')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
                Tables\Filters\Filter::make('start_time')
                    ->form([
                        Forms\Components\TimePicker::make('start_from')
                            ->label('Start From'),
                        Forms\Components\TimePicker::make('start_until')
                            ->label('Start Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn(Builder $query, $date): Builder => $query->whereTime('start_time', '>=', $date),
                            )
                            ->when(
                                $data['start_until'],
                                fn(Builder $query, $date): Builder => $query->whereTime('start_time', '<=', $date),
                            );
                    })->columns(2),
                Tables\Filters\Filter::make('end_time')
                    ->form([
                        Forms\Components\TimePicker::make('end_from')
                            ->label('End From'),
                        Forms\Components\TimePicker::make('end_until')
                            ->label('End Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['end_from'],
                                fn(Builder $query, $date): Builder => $query->whereTime('end_time', '>=', $date),
                            )
                            ->when(
                                $data['end_until'],
                                fn(Builder $query, $date): Builder => $query->whereTime('end_time', '<=', $date),
                            );
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Shift')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ScheduleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
