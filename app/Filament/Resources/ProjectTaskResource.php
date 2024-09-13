<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Exports\ProjectTaskExporter;
use App\Filament\Imports\ProjectTaskImporter;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementProject\ProjectTask;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProjectTaskResource\Pages;
use App\Filament\Resources\ProjectTaskResource\RelationManagers;
use Filament\Tables\Actions\ActionGroup;

class ProjectTaskResource extends Resource
{
    protected static ?string $model = ProjectTask::class;

    protected static ?string $navigationBadgeTooltip = 'Total Project Task';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Project';

    protected static ?string $navigationParentItem = 'Projects';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('task_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('task_description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('due_date')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'overdue' => 'Overdue',
                            ])
                            ->required(),
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedEmployee', 'first_name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('task_name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('task_description')
                    ->limit(50)
                    ->toggleable()
                    ->html(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'overdue' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('assignedEmployee.first_name')
                    ->searchable()
                    ->toggleable()
                    ->label('Assigned To'),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'overdue' => 'Overdue',
                    ]),
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->relationship('assignedEmployee', 'first_name')
                    ->label('Assigned To'),
                Tables\Filters\Filter::make('due_date')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('due_date', '=', $date)
                            );
                    })
                    ->form([
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->displayFormat('d/m/Y'),
                    ]),
            ])
            ->headerActions([
                ExportAction::make()->exporter(ProjectTaskExporter::class),
                ImportAction::make()->importer(ProjectTaskImporter::class)
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('complete')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (ProjectTask $record) {
                            $record->status = 'completed';
                            $record->save();
                        })
                        ->requiresConfirmation()
                        ->hidden(fn(ProjectTask $record) => $record->status === 'completed'),
                    Tables\Actions\Action::make('reopen')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (ProjectTask $record) {
                            $record->status = 'in_progress';
                            $record->save();
                        })
                        ->requiresConfirmation()
                        ->visible(fn(ProjectTask $record) => $record->status === 'completed'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()->exporter(ProjectTaskExporter::class)
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListProjectTasks::route('/'),
            'create' => Pages\CreateProjectTask::route('/create'),
            'edit' => Pages\EditProjectTask::route('/{record}/edit'),
        ];
    }
}
