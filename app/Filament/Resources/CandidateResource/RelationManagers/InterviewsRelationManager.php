<?php

namespace App\Filament\Resources\CandidateResource\RelationManagers;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagementSDM\Employee;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class InterviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'interviews';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Candidate Interview Information')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('candidate_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('candidate.first_name')
                    ->label('Candidate')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('interview_date')
                    ->label('Interview Date')
                    ->toggleable()
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
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
