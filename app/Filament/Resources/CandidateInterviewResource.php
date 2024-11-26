<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagementSDM\Employee;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Clusters\Employee as cluster;
use App\Models\ManagementSDM\CandidateInterview;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Exports\CandidateInterviewExporter;
use App\Filament\Imports\CandidateInterviewImporter;
use App\Filament\Resources\CandidateInterviewResource\Pages;
use App\Filament\Resources\CandidateInterviewResource\RelationManagers;

class CandidateInterviewResource extends Resource
{
    protected static ?string $model = CandidateInterview::class;

    protected static ?string $navigationLabel = 'Interview';

    protected static ?string $modelLabel = 'Interview';
    
    protected static ?string $pluralModelLabel = 'Interview';

    protected static ?string $cluster = cluster::class;

    protected static ?int $navigationSort = 9; //29

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'candidateInterviews';

    protected static ?string $navigationGroup = 'Recruitment Management';

    protected static ?string $navigationIcon = 'gmdi-find-replace-o';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Candidate Interview Information')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('candidate_id')
                            ->relationship('candidate', 'first_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('interview_date')
                            ->required()
                            ->label('Interview Date'),
                        Forms\Components\Select::make('interviewer')
                            ->options(function () {
                                return Employee::all()->pluck('first_name', 'first_name');
                            })
                            ->searchable()
                            ->preload()
                            ->label('Interviewer Name')
                            ->placeholder('Enter interviewer name')
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
                                                ->url(fn() => route('filament.admin.resources.candidate-interviews.edit', ['record' => $record->id]), shouldOpenInNewTab: true)
                                        ])
                                        ->success()
                                        ->sendToDatabase($user);
                                }
                            }),
                        Forms\Components\Select::make('interview_type')
                            ->options([
                                'phone' => 'Phone',
                                'video' => 'Video',
                                'in_person' => 'In Person',
                            ])
                            ->required()
                            ->default('in_person'),
                        Forms\Components\Textarea::make('interview_notes')
                            ->label('Interview Notes')
                            ->placeholder('Enter interview notes here')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('result')
                            ->options([
                                'passed' => 'Passed',
                                'failed' => 'Failed',
                                'pending' => 'Pending',
                            ])
                            ->required()
                            ->default('pending')
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
                                                ->url(fn() => route('filament.admin.resources.candidate-interviews.edit', ['record' => $record->id]), shouldOpenInNewTab: true)
                                        ])
                                        ->success()
                                        ->sendToDatabase($user);
                                }
                            })
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
            ])->columns(2);
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
                Tables\Columns\TextColumn::make('candidate.first_name')
                    ->label('Candidate')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('interview_date')
                    ->label('Interview Date')
                    ->toggleable()
                    ->icon('heroicon-o-calendar')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('interviewer')
                    ->label('Interviewer')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('interview_type')
                    ->badge()
                    ->label('Interview Type')
                    ->toggleable()
                    ->colors([
                        'primary' => 'phone',
                        'success' => 'video',
                        'warning' => 'in_person',
                    ]),
                Tables\Columns\TextColumn::make('interview_notes')
                    ->label('Notes')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('result')
                    ->badge()
                    ->label('Result')
                    ->toggleable()
                    ->colors([
                        'success' => 'passed',
                        'danger' => 'failed',
                        'warning' => 'pending',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
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
                Tables\Filters\SelectFilter::make('interview_type')
                    ->options([
                        'phone' => 'Phone',
                        'video' => 'Video',
                        'in_person' => 'In Person',
                    ])
                    ->label('Interview Type')
                    ->multiple(),
                Tables\Filters\SelectFilter::make('result')
                    ->options([
                        'passed' => 'Passed',
                        'failed' => 'Failed',
                        'pending' => 'Pending',
                    ])
                    ->label('Result')
                    ->multiple(),
                Tables\Filters\Filter::make('interview_date')
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
                                fn(Builder $query, $date): Builder => $query->whereDate('interview_date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('interview_date', '<=', $date),
                            );
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_notes')
                    ->label('Has Notes')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('interview_notes'),
                        false: fn(Builder $query) => $query->whereNull('interview_notes'),
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('changeResult')
                        ->label('Change Result')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('result')
                                ->label('Result')
                                ->options([
                                    'passed' => 'Passed',
                                    'failed' => 'Failed',
                                    'pending' => 'Pending',
                                ])
                                ->required(),
                        ])
                        ->action(function (CandidateInterview $record, array $data) {
                            $record->update(['result' => $data['result']]);
                            Notification::make()
                                ->title('Interview result updated successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('addNotes')
                        ->label('Add Notes')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->form([
                            Forms\Components\Textarea::make('interview_notes')
                                ->label('Interview Notes')
                                ->required(),
                        ])
                        ->action(function (CandidateInterview $record, array $data) {
                            $record->update(['interview_notes' => $data['interview_notes']]);
                            Notification::make()
                                ->title('Interview notes added successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('reschedule')
                        ->label('Reschedule')
                        ->icon('heroicon-o-calendar')
                        ->color('primary')
                        ->form([
                            Forms\Components\DatePicker::make('interview_date')
                                ->label('New Interview Date')
                                ->required(),
                        ])
                        ->action(function (CandidateInterview $record, array $data) {
                            $record->update(['interview_date' => $data['interview_date']]);
                            Notification::make()
                                ->title('Interview rescheduled successfully')
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()->exporter(CandidateInterviewExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export department completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(CandidateInterviewImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Import department completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->icon('heroicon-o-cog-6-tooth')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exporter(CandidateInterviewExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export department completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidateInterviews::route('/'),
            'create' => Pages\CreateCandidateInterview::route('/create'),
            'edit' => Pages\EditCandidateInterview::route('/{record}/edit'),
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
            'candidate_id',
            'interview_date',
            'interviewer',
            'interview_type',  // phone, video, in_person
            'interview_notes',
            'result',  // passed, failed, pending 
        ];
    }
}
