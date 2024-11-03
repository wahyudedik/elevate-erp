<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Models\ManagementProject\Project;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\ManagementProject\ProjectResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProjectResourceRelationManager extends RelationManager
{
    protected static string $relationship = 'projectResource';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Project Resource')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name', fn($query) => $query->where('status', 'planning'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('resource_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('resource_type')
                            ->options([
                                'human' => 'Human',
                                'material' => 'Material',
                                'financial' => 'Financial',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('resource_cost')
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(42949672.95)
                            ->step(0.01),
                    ])->columns(2),
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('resource_name')
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
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('resource_name')
                    ->label('Resource Name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('resource_type')
                    ->badge()
                    ->toggleable()
                    ->label('Resource Type')
                    ->colors([
                        'primary' => 'human',
                        'success' => 'material',
                        'warning' => 'financial',
                    ]),
                Tables\Columns\TextColumn::make('resource_cost')
                    ->label('Resource Cost')
                    ->money('IDR')
                    ->toggleable()
                    ->sortable(),
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

            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Project'),
                Tables\Filters\SelectFilter::make('resource_type')
                    ->options([
                        'human' => 'Human',
                        'material' => 'Material',
                        'financial' => 'Financial',
                    ])
                    ->label('Resource Type'),
                Tables\Filters\Filter::make('resource_cost')
                    ->form([
                        Forms\Components\TextInput::make('resource_cost_from')
                            ->numeric()
                            ->label('Minimum Cost'),
                        Forms\Components\TextInput::make('resource_cost_to')
                            ->numeric()
                            ->label('Maximum Cost'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['resource_cost_from'],
                                fn(Builder $query, $cost): Builder => $query->where('resource_cost', '>=', $cost),
                            )
                            ->when(
                                $data['resource_cost_to'],
                                fn(Builder $query, $cost): Builder => $query->where('resource_cost', '<=', $cost),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['resource_cost_from'] ?? null) {
                            $indicators['resource_cost_from'] = 'Resource cost from: ' . $data['resource_cost_from'];
                        }
                        if ($data['resource_cost_to'] ?? null) {
                            $indicators['resource_cost_to'] = 'Resource cost to: ' . $data['resource_cost_to'];
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('assignProject')
                        ->form([
                            Forms\Components\Select::make('project_id')
                                ->label('Project')
                                ->options(Project::pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (ProjectResource $record, array $data): void {
                            $record->update(['project_id' => $data['project_id']]);
                            Notification::make()
                                ->title('Resource assigned to project')
                                ->success()
                                ->send();
                        })
                        ->icon('heroicon-o-link')
                        ->color('success'),
                    Tables\Actions\Action::make('updateCost')
                        ->form([
                            Forms\Components\TextInput::make('resource_cost')
                                ->label('Resource Cost')
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function (ProjectResource $record, array $data): void {
                            $record->update(['resource_cost' => $data['resource_cost']]);
                            Notification::make()
                                ->title('Resource cost updated')
                                ->success()
                                ->send();
                        })
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning'),
                ])
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateType')
                        ->form([
                            Forms\Components\Select::make('resource_type')
                                ->label('Resource Type')
                                ->options([
                                    'human' => 'Human',
                                    'material' => 'Material',
                                    'financial' => 'Financial',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update(['resource_type' => $data['resource_type']]);
                            });
                            Notification::make()
                                ->title('Resource types updated')
                                ->success()
                                ->send();
                        })
                        ->icon('heroicon-o-tag')
                        ->color('info'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
