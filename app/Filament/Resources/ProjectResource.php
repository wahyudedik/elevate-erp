<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Filament\Exports\ProjectExporter;
use App\Filament\Imports\ProjectImporter;
use App\Models\ManagementProject\Project;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Actions\Action;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers\ProjectMilestoneRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\ProjectResourceRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\ProjectTaskRelationManager;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationBadgeTooltip = 'Total Project';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Project';

    protected static ?string $navigationParentItem = null;

    protected static ?string $navigationIcon = 'lineawesome-project-diagram-solid';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->placeholder('Project Name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan('full'),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->placeholder('Project Description')
                    ->maxLength(65535)
                    ->columnSpan('full'),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Start Date')
                    ->placeholder('Start Date')
                    ->required(),

                Forms\Components\DatePicker::make('end_date')
                    ->label('End Date')
                    ->placeholder('End Date')
                    ->nullable(),

                Forms\Components\Select::make('client_id')
                    ->label('Client')
                    ->placeholder('Select Client')
                    ->relationship('customers', 'name', fn(Builder $query) => $query->where('status', 'active'))
                    ->searchable()
                    ->required()
                    ->preload(),

                Forms\Components\TextInput::make('budget')
                    ->label('Budget')
                    ->placeholder('Project Budget')
                    ->prefix('IDR')
                    ->nullable()
                    ->maxLength(255),

                Forms\Components\Select::make('manager_id')
                    ->label('Manager')
                    ->placeholder('Select Manager')
                    ->relationship('manager', 'first_name', fn(Builder $query) => $query->where('position', 'manager')->where('status', 'active'))
                    ->searchable()
                    ->required()
                    ->preload(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->placeholder('Select Status')
                    ->options([
                        'planning' => 'Planning',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'on_hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    ])
                    ->searchable()
                    ->required(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('customers.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('budget')
                    ->money('idr')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('manager.first_name')
                    ->label('Manager')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'planning',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'on_hold',
                        'secondary' => 'cancelled',
                    ])
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Project Status')
                    ->placeholder('All Statuses')
                    ->trueLabel('Completed')
                    ->falseLabel('In Progress')
                    ->queries(
                        true: fn(Builder $query) => $query->where('status', 'completed'),
                        false: fn(Builder $query) => $query->whereIn('status', ['planning', 'in_progress', 'on_hold']),
                        blank: fn(Builder $query) => $query
                    ),
                Tables\Filters\SelectFilter::make('client')
                    ->relationship('customers', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('manager')
                    ->relationship('manager', 'first_name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('budget_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('budget_from')
                                    ->numeric()
                                    ->label('Budget from')
                                    ->placeholder('Min budget')
                                    ->suffixIcon('heroicon-m-currency-dollar'),
                                Forms\Components\TextInput::make('budget_to')
                                    ->numeric()
                                    ->label('Budget to')
                                    ->placeholder('Max budget')
                                    ->suffixIcon('heroicon-m-currency-dollar'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['budget_from'],
                                fn(Builder $query, $amount): Builder => $query->where('budget', '>=', $amount),
                            )
                            ->when(
                                $data['budget_to'],
                                fn(Builder $query, $amount): Builder => $query->where('budget', '<=', $amount),
                            );
                    })->columns(2),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_from')
                            ->label('Start Date From'),
                        Forms\Components\DatePicker::make('start_until')
                            ->label('Start Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['start_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('change_status')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'planning' => 'Planning',
                                    'in_progress' => 'In Progress',
                                    'completed' => 'Completed',
                                    'on_hold' => 'On Hold',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required(),
                        ])
                        ->action(function (Project $record, array $data): void {
                            $record->update(['status' => $data['status']]);
                            Notification::make()
                                ->title('Project status updated successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->headerActions([
                ExportAction::make()->exporter(ProjectExporter::class),
                ImportAction::make()->importer(ProjectImporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('change_status')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'planning' => 'Planning',
                                    'in_progress' => 'In Progress',
                                    'completed' => 'Completed',
                                    'on_hold' => 'On Hold',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                            Notification::make()
                                ->title('Projects status updated successfully')
                                ->success()
                                ->send();
                        }),
                ]),
                ExportBulkAction::make()
                    ->exporter(ProjectExporter::class)
            ])
            ->emptyStateActions([
                CreateAction::make()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProjectTaskRelationManager::class,
            ProjectMilestoneRelationManager::class,
            ProjectResourceRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
