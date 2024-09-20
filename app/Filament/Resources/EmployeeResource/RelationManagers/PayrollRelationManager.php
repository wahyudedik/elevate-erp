<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use App\Models\ManagementSDM\Employee;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PayrollRelationManager extends RelationManager
{
    protected static string $relationship = 'payroll';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Payroll Details')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\TextInput::make('basic_salary')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->default(function ($get) {
                                $employee = Employee::find($get('employee_id'));
                                return $employee ? $employee->salary : null;
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($set, $get) {
                                $employee = Employee::find($get('employee_id'));
                                if ($employee) {
                                    $set('basic_salary', $employee->salary);
                                }
                            }),
                        // ->mask(RawJs::make('$money($input)')),
                        Forms\Components\TextInput::make('allowances')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->default(function ($get) {
                                $employee = Employee::find($get('employee_id'));
                                return $employee ? $employee->salary : null;
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($set, $get) {
                                $employee = Employee::find($get('employee_id'));
                                if ($employee) {
                                    $set('allowances', $employee->salary);
                                }
                            }),
                        // ->mask(RawJs::make('$money($input)')),
                        Forms\Components\TextInput::make('deductions')
                            ->default(0)
                            ->numeric()
                            ->prefix('IDR'),
                        // ->mask(RawJs::make('$money($input)')),
                        Forms\Components\TextInput::make('net_salary')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->default(function ($get) {
                                $allowances = $get('allowances') ?? 0;
                                $deductions = $get('deductions') ?? 0;
                                return $allowances - $deductions;
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($set, $get) {
                                $allowances = $get('allowances') ?? 0;
                                $deductions = $get('deductions') ?? 0;
                                $set('net_salary', $allowances - $deductions);
                            }),
                        // ->mask(RawJs::make('$money($input)')),
                        Forms\Components\DatePicker::make('payment_date')
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\TextInput::make('payment_method')
                            ->maxLength(255),
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
            ->recordTitleAttribute('basic_salary')
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->searchable()
                    ->sortable()
                    ->label('Employee')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('basic_salary')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('allowances')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('deductions')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('net_salary')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->icon('heroicon-o-calendar')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->colors([
                        'danger' => 'pending',
                        'success' => 'paid',
                    ])
                    ->icons([
                        'heroicon-o-x-circle' => 'pending',
                        'heroicon-o-check-circle' => 'paid',
                    ])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable()
                    ->toggleable(),
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
                    ->modalHeading('Add New Payroll')
                    ->modalSubmitActionLabel('Add Payroll')
                    ->modalWidth('xl')
                    ->modalDescription('Add new payroll for employee')
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
