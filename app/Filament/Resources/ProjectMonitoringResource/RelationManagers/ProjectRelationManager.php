<?php

namespace App\Filament\Resources\ProjectMonitoringResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProjectRelationManager extends RelationManager
{
    protected static string $relationship = 'project';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),

                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Project Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->placeholder('Project Description')
                            ->maxLength(65535)
                            ->columnSpan('full'),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->placeholder('Start Date')
                            ->default(now())
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->placeholder('End Date')
                            ->default(now())
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
                            ->relationship('manager', 'first_name', fn(Builder $query) => $query->where('position_id', 'manager')->where('status', 'active'))
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
                            ->default('planning')
                            ->searchable()
                            ->required(),
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
            ->recordTitleAttribute('name')
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
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->html()
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
                    ->toggledHiddenByDefault(true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
