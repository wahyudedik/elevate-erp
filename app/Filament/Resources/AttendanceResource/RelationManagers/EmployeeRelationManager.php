<?php

namespace App\Filament\Resources\AttendanceResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\ViewAction;

class EmployeeRelationManager extends RelationManager
{
    protected static string $relationship = 'employee';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('employee_code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                        Forms\Components\TextInput::make('national_id_number')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Employment Details')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('position_id')
                            ->relationship('position', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('department_id')
                            ->relationship('department', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('date_of_joining')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('salary')
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(9999999999999.99),
                        Forms\Components\Select::make('employment_status')
                            ->options([
                                'permanent' => 'Permanent',
                                'contract' => 'Contract',
                                'internship' => 'Internship',
                            ])
                            ->default('permanent'),
                        Forms\Components\Select::make('manager_id')
                            ->relationship('manager', 'position_id')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'terminated' => 'Terminated',
                                'resigned' => 'Resigned',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('User')
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->avatar()
                                    ->disk('public')
                                    ->directory('user-images')
                                    ->visibility('public')
                                    ->maxSize(5024)
                                    ->columnSpanFull()
                                    ->label('Profile Image'),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DateTimePicker::make('email_verified_at'),
                                Forms\Components\Select::make('roles')
                                    ->relationship('roles', 'name')
                                    // ->multiple()
                                    ->preload()
                                    ->searchable(),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn(string $context): bool => $context === 'create'),
                                Forms\Components\Select::make('usertype')
                                    ->options([
                                        'staff' => 'Staff',
                                        'member' => 'Member',
                                    ])
                                    ->required()
                                    ->default('staff'),
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Address Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Employee Documents')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_picture')
                            ->image()
                            ->disk('public')
                            ->directory('employee-profile-pictures')
                            ->maxSize(1024)
                            ->label('Profile Picture')
                            ->imagePreviewHeight('250')
                            ->downloadable()
                            ->avatar()
                            ->openable()
                            ->visibility('public'),
                        Forms\Components\FileUpload::make('contract')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('public')
                            ->directory('employee-contracts')
                            ->maxSize(5120)
                            ->label('Employment Contract')
                            ->downloadable()
                            ->openable()
                            ->visibility('public'),
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
            ->recordTitleAttribute('first_name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('profile_picture')
                    ->label('Profile Picture')
                    ->circular()
                    ->disk('public')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('employee_code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Employee code copied to clipboard')
                    ->copyMessageDuration(1500)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied to clipboard')
                    ->copyMessageDuration(1500)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->toggleable()
                    ->icon('heroicon-o-user')
                    ->colors([
                        'primary' => 'male',
                        'danger' => 'female',
                        'warning' => 'other',
                    ]),
                Tables\Columns\TextColumn::make('national_id_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('position.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date_of_joining')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('salary')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('employment_status')
                    ->badge()
                    ->colors([
                        'success' => 'permanent',
                        'warning' => 'contract',
                        'danger' => 'internship',
                    ])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('manager.first_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => fn($state) => in_array($state, ['terminated', 'resigned']),
                    ])
                    ->toggleable(),
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
                Tables\Filters\SelectFilter::make('employment_status')
                    ->options([
                        'permanent' => 'Permanent',
                        'contract' => 'Contract',
                        'internship' => 'Internship',
                    ])
                    ->label('Employment Status')
                    ->indicator('Employment Status'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'terminated' => 'Terminated',
                        'resigned' => 'Resigned',
                    ])
                    ->label('Employee Status')
                    ->indicator('Employee Status'),
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ])
                    ->label('Gender')
                    ->indicator('Gender'),
                Tables\Filters\Filter::make('date_of_joining')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_of_joining', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_of_joining', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from_date'] ?? null) {
                            $indicators['from_date'] = 'Joined from ' . Carbon::parse($data['from_date'])->toFormattedDateString();
                        }
                        if ($data['to_date'] ?? null) {
                            $indicators['to_date'] = 'Joined until ' . Carbon::parse($data['to_date'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\Filter::make('salary')
                    ->form([
                        Forms\Components\TextInput::make('min_salary')
                            ->label('Minimum Salary')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_salary')
                            ->label('Maximum Salary')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_salary'],
                                fn(Builder $query, $salary): Builder => $query->where('salary', '>=', $salary),
                            )
                            ->when(
                                $data['max_salary'],
                                fn(Builder $query, $salary): Builder => $query->where('salary', '<=', $salary),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['min_salary'] ?? null) {
                            $indicators['min_salary'] = 'Min salary: ' . number_format($data['min_salary'], 2, ',', '.');
                        }
                        if ($data['max_salary'] ?? null) {
                            $indicators['max_salary'] = 'Max salary: ' . number_format($data['max_salary'], 2, ',', '.');
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_manager')
                    ->label('Has Manager')
                    ->indicator('Manager'),
                Tables\Filters\SelectFilter::make('position')
                    ->label('Position')
                    ->options(function () {
                        return Employee::distinct()->pluck('position_id', 'id')->toArray();
                    })
                    ->indicator('Position'),
                Tables\Filters\SelectFilter::make('department')
                    ->label('Department')
                    ->options(function () {
                        return Employee::distinct()->pluck('department_id', 'id')->toArray();
                    })
                    ->indicator('Department'),
            ])
            ->headerActions([])
            ->actions([
                ViewAction::make()->icon('heroicon-o-eye')->primary(),
            ])
            ->bulkActions([]);
    }
}
