<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementSDM\EmployeePosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EmployeePositionRelationManager extends RelationManager
{
    protected static string $relationship = 'employeePosition';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Employee Position Details')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Employee'),
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
                            ->searchable(),
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->default(now())
                            ->label('Start Date'),
                        Forms\Components\DatePicker::make('end_date')
                            ->nullable()
                            ->label('End Date'),
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
            ->recordTitleAttribute('employee_id')
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
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->searchable()
                    ->sortable()
                    ->label('Employee')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->sortable()
                    ->label('Position')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Employee'),
                Tables\Filters\SelectFilter::make('position')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Position'),
                Tables\Filters\Filter::make('active')
                    ->query(fn(Builder $query): Builder => $query->whereNull('end_date'))
                    ->toggle()
                    ->label('Show Only Active Positions'),
                Tables\Filters\Filter::make('start_date')
                    ->form([
                        DatePicker::make('start'),
                        DatePicker::make('end')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['end'],
                                fn(Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    })->columns(2),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('endPosition')
                        ->label('End Position')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\DatePicker::make('end_date')
                                ->label('End Date')
                                ->required()
                                ->minDate(fn(EmployeePosition $record): string => $record->start_date)
                                ->default(now()),
                        ])
                        ->action(function (EmployeePosition $record, array $data): void {
                            $record->update([
                                'end_date' => $data['end_date'],
                            ]);
                            $record->employee->update([
                                'position_id' => null,
                            ]);
                            Notification::make()
                                ->title('Position ended')
                                ->success()
                                ->send();
                        })
                        ->hidden(fn(EmployeePosition $record): bool => $record->end_date !== null),
                    Tables\Actions\Action::make('extendPosition')
                        ->label('Extend Position')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->form([
                            Forms\Components\DatePicker::make('new_end_date')
                                ->label('New End Date')
                                ->required()
                                ->minDate(fn(EmployeePosition $record): string => $record->end_date ?? $record->start_date),
                        ])
                        ->action(function (EmployeePosition $record, array $data): void {
                            $record->update([
                                'end_date' => $data['new_end_date'],
                            ]);
                            Notification::make()
                                ->title('Position extended')
                                ->success()
                                ->send();
                        })
                        ->hidden(fn(EmployeePosition $record): bool => $record->end_date === null),
                ]),
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
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
                    ->icon('heroicon-o-plus')
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
