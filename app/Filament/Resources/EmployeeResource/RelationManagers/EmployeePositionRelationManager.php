<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EmployeePositionRelationManager extends RelationManager
{
    protected static string $relationship = 'employeePosition';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload()
                    ->label('Employee')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\Select::make('position')
                    ->options([
                        'manager' => 'Manager',
                        'supervisor' => 'Supervisor',
                        'team_lead' => 'Team Lead',
                        'developer' => 'Developer',
                        'designer' => 'Designer',
                        'analyst' => 'Analyst',
                        'hr_specialist' => 'HR Specialist',
                        'accountant' => 'Accountant',
                        'sales_representative' => 'Sales Representative',
                        'customer_support' => 'Customer Support',
                        'marketing_specialist' => 'Marketing Specialist',
                        'project_manager' => 'Project Manager',
                        'quality_assurance' => 'Quality Assurance',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->label('Position'),

                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->label('Start Date')
                    ->default(now()),

                Forms\Components\DatePicker::make('end_date')
                    ->nullable()
                    ->label('End Date')
                    ->afterOrEqual('start_date'),

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
            ->recordTitleAttribute('employee_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->before(function (CreateAction $action) {
                        $employee = $action->getRecord()?->employee;
                        $latestPosition = $employee?->employeePositions()->latest()->first();

                        if ($latestPosition && is_null($latestPosition->end_date)) {
                            Notification::make()
                                ->title('Cannot create new position')
                                ->body('The employee already has an active position without an end date.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    }),
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
