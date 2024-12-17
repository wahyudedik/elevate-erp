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
                Forms\Components\Section::make('Informasi Wawancara Kandidat')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->label('Cabang')
                            ->preload(),
                        Forms\Components\Select::make('candidate_id')
                            ->relationship('candidate', 'first_name')
                            ->required()
                            ->label('Nama Kandidat')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('interview_date')
                            ->required()
                            ->label('Tanggal Wawancara'),
                        Forms\Components\Select::make('interviewer')
                            ->options(function () {
                                return Employee::all()->pluck('first_name', 'first_name');
                            })
                            ->searchable()
                            ->preload()
                            ->label('Nama Pewawancara')
                            ->placeholder('Masukkan nama pewawancara')
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
                                'phone' => 'Telepon',
                                'video' => 'Video',
                                'in_person' => 'Tatap Muka',
                            ])
                            ->required()
                            ->default('in_person'),
                        Forms\Components\Textarea::make('interview_notes')
                            ->label('Catatan Wawancara')
                            ->placeholder('Masukkan catatan wawancara disini')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('result')
                            ->options([
                                'passed' => 'Lulus',
                                'failed' => 'Gagal',
                                'pending' => 'Tertunda',
                            ])
                            ->required()
                            ->default('pending')
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
                    ->label('Nama Kandidat')
                    ->toggleable()
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('interview_date')
                    ->label('Tanggal Wawancara')
                    ->toggleable()
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar'),
                Tables\Columns\TextColumn::make('interviewer')
                    ->label('Pewawancara')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('interview_type')
                    ->badge()
                    ->label('Tipe Wawancara')
                    ->toggleable()
                    ->colors([
                        'primary' => fn($state) => $state === 'phone',
                        'success' => fn($state) => $state === 'video',
                        'warning' => fn($state) => $state === 'in_person',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'phone' => 'Telepon',
                        'video' => 'Video',
                        'in_person' => 'Tatap Muka',
                        default => $state
                    }),
                Tables\Columns\TextColumn::make('interview_notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
                Tables\Columns\TextColumn::make('result')
                    ->badge()
                    ->label('Hasil')
                    ->toggleable()
                    ->colors([
                        'success' => fn($state) => $state === 'passed',
                        'danger' => fn($state) => $state === 'failed',
                        'warning' => fn($state) => $state === 'pending',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'passed' => 'Lulus',
                        'failed' => 'Gagal',
                        'pending' => 'Tertunda',
                        default => $state
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i')
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
