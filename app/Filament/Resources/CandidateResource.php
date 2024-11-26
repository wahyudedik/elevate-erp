<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagementSDM\Candidate;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Models\ManagementSDM\Recruitment;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementSDM\Applications;
use App\Filament\Exports\CandidateExporter;
use App\Filament\Imports\CandidateImporter;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementSDM\CandidateInterview;
use App\Filament\Resources\CandidateResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CandidateResource\RelationManagers;
use App\Filament\Resources\CandidateResource\RelationManagers\EmployeeRelationManager;
use App\Filament\Resources\CandidateResource\RelationManagers\InterviewsRelationManager;
use App\Filament\Resources\CandidateResource\RelationManagers\ApplicationRelationManager;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationLabel = 'Kandidat';

    protected static ?string $modelLabel = 'Kandidat';
    
    protected static ?string $pluralModelLabel = 'Kandidat';

    protected static ?string $cluster = Employee::class;

    protected static ?int $navigationSort = 8; //29

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'candidates';

    protected static ?string $navigationGroup = 'Recruitment Management';

    protected static ?string $navigationIcon = 'iconpark-find-o';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
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

                Forms\Components\Section::make('Application Details')
                    ->schema([
                        Forms\Components\Select::make('position_applied')
                            ->required()
                            ->options(function () {
                                return Recruitment::where('status', 'open')->pluck('job_title', 'id');
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('recruiter_id')
                            ->relationship('recruiter', 'first_name')
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                $user = User::where('employee_id', Auth::user()->id)->first();
                                if ($record) {
                                    Notification::make()
                                        ->title('Assigned New Candidate')
                                        ->body('You have been assigned a new candidate')
                                        ->icon('heroicon-o-user-group')
                                        ->actions([
                                            \Filament\Notifications\Actions\Action::make('view')
                                                ->button()
                                                ->url(fn() => route('filament.admin.resources.candidates.edit', ['record' => $record->id]), shouldOpenInNewTab: true)
                                        ])
                                        ->success()
                                        ->sendToDatabase($user);
                                }
                            }),
                        Forms\Components\DatePicker::make('application_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\FileUpload::make('resume')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->directory('candidate-resumes')
                            ->downloadable()
                            ->openable(),
                    ])->columns(2),

                Forms\Components\Section::make('Address Information')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(255),
                        Forms\Components\Select::make('country')
                            ->searchable()
                            ->options([
                                'Indonesia' => 'Indonesia'
                            ]),
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
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->icon('heroicon-o-envelope')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->icon('heroicon-o-phone')
                    ->searchable()
                    ->toggleable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'primary' => 'male',
                        'secondary' => 'female',
                        'warning' => 'other',
                    ])
                    ->icon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('national_id_number')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('position_applied')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'applied',
                        'primary' => fn($state) => in_array($state, ['interviewing', 'offered']),
                        'success' => 'hired',
                    ])
                    ->icon('heroicon-o-user-circle'),
                Tables\Columns\TextColumn::make('recruiter.first_name')
                    ->label('Recruiter')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('application_date')
                    ->date()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->toggleable()
                    ->searchable(),
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
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ])
                    ->label('Gender')
                    ->placeholder('All Genders'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'applied' => 'Applied',
                        'interviewing' => 'Interviewing',
                        'offered' => 'Offered',
                        'hired' => 'Hired',
                        'rejected' => 'Rejected',
                    ])
                    ->label('Status')
                    ->placeholder('All Statuses')
                    ->multiple(),
                Tables\Filters\Filter::make('application_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('application_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('application_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Application from ' . Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Application until ' . Carbon::parse($data['until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\SelectFilter::make('recruiter')
                    ->relationship('recruiter', 'first_name')
                    ->label('Recruiter')
                    ->placeholder('All Recruiters')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('has_resume')
                    ->label('Has Resume')
                    ->placeholder('All Candidates')
                    ->trueLabel('With Resume')
                    ->falseLabel('Without Resume')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('resume'),
                        false: fn(Builder $query) => $query->whereNull('resume'),
                    ),
                Tables\Filters\Filter::make('city_country')
                    ->form([
                        Forms\Components\TextInput::make('city')
                            ->label('City'),
                        Forms\Components\TextInput::make('country')
                            ->label('Country'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['city'],
                                fn(Builder $query, $city): Builder => $query->where('city', 'like', "%{$city}%"),
                            )
                            ->when(
                                $data['country'],
                                fn(Builder $query, $country): Builder => $query->where('country', 'like', "%{$country}%"),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['city'] ?? null) {
                            $indicators['city'] = 'City: ' . $data['city'];
                        }
                        if ($data['country'] ?? null) {
                            $indicators['country'] = 'Country: ' . $data['country'];
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('download_resume')
                        ->label('Download Resume')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Candidate $record) {
                            if ($record->resume) {
                                return response()->download(storage_path('app/public/' . $record->resume));
                            }
                        })
                        ->requiresConfirmation()
                        ->hidden(fn(Candidate $record): bool => $record->resume === null),
                    Tables\Actions\Action::make('change_status')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'applied' => 'Applied',
                                    'interviewing' => 'Interviewing',
                                    'offered' => 'Offered',
                                    'hired' => 'Hired',
                                    'rejected' => 'Rejected',
                                ])
                                ->required(),
                        ])
                        ->action(function (Candidate $record, array $data): void {
                            $record->update(['status' => $data['status']]);

                            if ($data['status'] === 'interviewing') {
                                CandidateInterview::create([
                                    'candidate_id' => $record->id,
                                    'interview_date' => now(),
                                    'interviewer' => null,
                                    'interview_type' => 'in_person',
                                    'interview_notes' => null,
                                    'result' => 'pending'
                                ]);
                            }

                            if (in_array($data['status'], ['applied', 'interviewing', 'offered', 'hired', 'rejected'])) {
                                Applications::where('candidate_id', $record->id)->update([
                                    'status' => $data['status'],
                                ]);
                            }
                            Notification::make()
                                ->title('Status updated successfully')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])

            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()->exporter(CandidateExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export candidate completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(CandidateImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Import candidate completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->icon('heroicon-o-cog-6-tooth')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                ExportBulkAction::make()->exporter(CandidateExporter::class)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->after(function () {
                        Notification::make()
                            ->title('Export candidate completed' . ' ' . now())
                            ->success()
                            ->sendToDatabase(Auth::user());
                    }),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ApplicationRelationManager::class, //done
            InterviewsRelationManager::class, //done
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'company_id',
            'branch_id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'date_of_birth',
            'gender',
            'national_id_number',  // Nomor KTP/Paspor
            'position_applied',  // Posisi yang dilamar
            'status',  // applied, interviewing, offered, hired, rejected
            'recruiter_id',  // ID dari recruiter yang menangani
            'application_date',
            'resume',  // Resume/CV kandidat
            'address',
            'city',
            'state',
            'postal_code',
            'country',
        ];
    }
}
