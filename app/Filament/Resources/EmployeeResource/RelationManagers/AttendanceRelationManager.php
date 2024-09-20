<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AttendanceRelationManager extends RelationManager
{
    protected static string $relationship = 'attendance';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Attendance Details')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\DatePicker::make('date')
                            ->date()
                            ->required(),
                        Forms\Components\TimePicker::make('check_in'),
                        Forms\Components\TimePicker::make('check_out'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'present' => 'Present',
                                'absent' => 'Absent',
                                'late' => 'Late',
                                'on_leave' => 'On Leave',
                            ])
                            ->required()
                            ->default('present'),
                        Forms\Components\Textarea::make('note')
                            ->maxLength(255)
                            ->columnSpanFull(),
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
            ->recordTitleAttribute('date')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label('Employee'),
                Tables\Columns\TextColumn::make('date')
                    ->date('Y-m-d')
                    ->icon('heroicon-o-calendar')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_in')
                    ->icon('heroicon-o-clock')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Check In'),
                Tables\Columns\TextColumn::make('check_out')
                    ->icon('heroicon-o-clock')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Check Out'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'primary' => 'on_leave',
                    ]),
                Tables\Columns\TextColumn::make('note')
                    ->limit(30)
                    ->toggleable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Add Attendance')
                    ->modalSubmitActionLabel('Add Attendance')
                    ->modalWidth('xl')
                    ->modalDescription('Add new attendance for employee')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
