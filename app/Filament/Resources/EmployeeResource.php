<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagementSDM\Employee;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\EmployeeExporter;
use App\Filament\Imports\EmployeeImporter;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementSDM\EmployeePosition;
use Filament\Notifications\DatabaseNotification;
use App\Filament\Resources\EmployeeResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Filament\Resources\EmployeeResource\RelationManagers\UserRelationManager;
use App\Filament\Resources\EmployeeResource\RelationManagers\PayrollRelationManager;
use App\Filament\Resources\EmployeeResource\RelationManagers\AttendanceRelationManager;
use App\Filament\Resources\EmployeeResource\RelationManagers\EmployeePositionRelationManager;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationBadgeTooltip = 'Total Employee';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'employee';

    protected static ?string $navigationGroup = 'Management SDM';

    protected static ?string $navigationParentItem = 'Employee Management';

    public static function form(Form $form): Form
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
                        Forms\Components\Select::make('department')
                            ->options([
                                'human_resources' => 'Human Resources',
                                'finance' => 'Finance',
                                'marketing' => 'Marketing',
                                'sales' => 'Sales',
                                'operations' => 'Operations',
                                'information_technology' => 'Information Technology',
                                'research_and_development' => 'Research and Development',
                                'customer_service' => 'Customer Service',
                                'legal' => 'Legal',
                                'administration' => 'Administration',
                            ])
                            ->required()
                            ->searchable(),
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
                            ->relationship('manager', 'first_name')
                            ->label('Manager')
                            ->options(function () {
                                return Employee::where('position', 'Manager')->pluck('first_name', 'id');
                            })->searchable()
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
                            // ->createOptionForm([
                            //     Forms\Components\TextInput::make('name')
                            //         ->required()
                            //         ->maxLength(255),
                            //     Forms\Components\TextInput::make('email')
                            //         ->email()
                            //         ->required()
                            //         ->maxLength(255),
                            //     Forms\Components\TextInput::make('password')
                            //         ->password()
                            //         ->required()
                            //         ->maxLength(255),
                            // ]),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
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
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('department')
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
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
                        return Employee::distinct()->pluck('position', 'position')->toArray();
                    })
                    ->indicator('Position'),
                Tables\Filters\SelectFilter::make('department')
                    ->label('Department')
                    ->options(function () {
                        return Employee::distinct()->pluck('department', 'department')->toArray();
                    })
                    ->indicator('Department'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('changeStatus')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Employee Status')
                                ->options([
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                    'terminated' => 'Terminated',
                                    'resigned' => 'Resigned',
                                ])
                                ->required(),
                        ])
                        ->action(function (Employee $record, array $data) {
                            $record->update(['status' => $data['status']]);
                            Notification::make()
                                ->title('Employee status updated successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('promoteEmployee')
                        ->label('Promote Employee')
                        ->icon('heroicon-o-arrow-up-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('new_position')
                                ->label('New Position')
                                ->required(),
                            Forms\Components\TextInput::make('salary_increment')
                                ->label('Salary Increment')
                                ->numeric()
                                ->prefix('IDR')
                                ->required(),
                        ])
                        ->action(function (Employee $record, array $data) {
                            $record->update([
                                'position' => $data['new_position'],
                                'salary' => $record->salary + $data['salary_increment'],
                            ]);
                            Notification::make()
                                ->title('Employee promoted successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('downloadContract')
                        ->label('Download Contract')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('primary')
                        ->action(function (Employee $record) {
                            $contractPath = $record->contract; // Assuming 'contract_path' is the column name in your database
                            $fullPath = public_path('storage/' . $contractPath);

                            if (file_exists($fullPath)) {
                                return response()->download($fullPath, "{$record->first_name}_contract.pdf");
                            } else {
                                Notification::make()
                                    ->title('Contract not found')
                                    ->warning()
                                    ->send();

                                return back();
                            }
                        }),
                ]),
            ])
            ->headerActions([
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(EmployeeExporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Employees exported successfully' . ' ' . date('Y-m-d H:i:s'))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(EmployeeImporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('warning')
                        ->after(function () {
                            Notification::make()
                                ->title('Employees imported successfully' . ' ' . date('Y-m-d H:i:s'))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->icon('heroicon-o-cog-6-tooth'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(EmployeeExporter::class)
                        ->color('success')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->after(function () {
                            Notification::make()
                                ->title('Employees exported successfully')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->color('primary')
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EmployeePositionRelationManager::class,
            // UserRelationManager::class,
            AttendanceRelationManager::class,
            PayrollRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
