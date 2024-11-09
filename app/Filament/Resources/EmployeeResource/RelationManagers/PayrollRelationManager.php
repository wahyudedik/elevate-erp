<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use App\Models\ManagementSDM\Payroll;
use App\Models\ManagementSDM\Employee;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
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
                Forms\Components\Section::make('Payroll Information')
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
                            ->label('Employee ID'),
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
                    ->preload(),
                Tables\Filters\Filter::make('basic_salary')
                    ->label('Basic Salary')
                    ->form([
                        Forms\Components\TextInput::make('min')
                            ->numeric()
                            ->label('Minimum')
                            ->placeholder('0'),
                        Forms\Components\TextInput::make('max')
                            ->numeric()
                            ->label('Maximum')
                            ->placeholder('1000000'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $min): Builder => $query->where('basic_salary', '>=', $min),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $max): Builder => $query->where('basic_salary', '<=', $max),
                            );
                    })->columns(2),
                Tables\Filters\Filter::make('net_salary')
                    ->label('Net Salary')
                    ->form([
                        Forms\Components\TextInput::make('min')
                            ->numeric()
                            ->label('Minimum')
                            ->placeholder('0'),
                        Forms\Components\TextInput::make('max')
                            ->numeric()
                            ->label('Maximum')
                            ->placeholder('1000000'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $min): Builder => $query->where('net_salary', '>=', $min),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $max): Builder => $query->where('net_salary', '<=', $max),
                            );
                    })->columns(2),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('payment_date', $date)
                            );
                    })->columns(2),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
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
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('markAsPaid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(Payroll $record) => $record->payment_status === 'pending')
                        ->action(function (Payroll $record) {
                            $record->update(['payment_status' => 'paid']);
                            Notification::make()
                                ->title('Payroll marked as paid')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('downloadPayslip')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn(Payroll $record) => route('filament.admin.resources.payrolls.download-payslip', $record))
                        ->openUrlInNewTab(),
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
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()->icon('heroicon-o-plus'),
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
