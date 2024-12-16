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

    protected static ?string $title = 'Gaji';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penggajian')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->label('Cabang'),
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
                            })
                            ->label('Gaji Pokok'),
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
                            })
                            ->label('Tunjangan'),
                        Forms\Components\TextInput::make('deductions')
                            ->default(0)
                            ->numeric()
                            ->prefix('IDR')
                            ->label('Potongan'),
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
                            })
                            ->label('Gaji Bersih'),
                        Forms\Components\DatePicker::make('payment_date')
                            ->required()
                            ->label('Tanggal Pembayaran'),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Tertunda',
                                'paid' => 'Dibayar',
                            ])
                            ->required()
                            ->default('pending')
                            ->label('Status Pembayaran'),
                        Forms\Components\TextInput::make('payment_method')
                            ->maxLength(255)
                            ->label('Metode Pembayaran'),
                    ])->columns(2),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat pada')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir diubah pada')
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
                    ->alignCenter()
                    ->size('lg'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->iconColor('primary')
                    ->sortable()
                    ->size('lg'),
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->searchable()
                    ->sortable()
                    ->label('Karyawan')
                    ->toggleable()
                    ->size('lg'),
                Tables\Columns\TextColumn::make('basic_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('success')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('allowances')
                    ->label('Tunjangan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('success')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('deductions')
                    ->label('Potongan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('danger')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('net_salary')
                    ->label('Gaji Bersih')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->color('success')
                    ->size('lg')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tanggal Pembayaran')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->size('lg'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->colors([
                        'danger' => 'pending',
                        'success' => 'paid',
                    ])
                    ->icons([
                        'heroicon-o-x-circle' => 'pending',
                        'heroicon-o-check-circle' => 'paid',
                    ])
                    ->toggleable()
                    ->size('lg'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->searchable()
                    ->toggleable()
                    ->size('lg'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('lg'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('lg')
            ])->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                ])
            ])
            ->headerActions([
                // CreateAction::make()->icon('heroicon-o-plus'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                //     Tables\Actions\ForceDeleteBulkAction::make(),
                //     Tables\Actions\RestoreBulkAction::make(),
                // ]),
            ])
            ->emptyStateActions([
                // CreateAction::make()->icon('heroicon-o-plus'),
            ]);
    }
}
