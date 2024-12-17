<?php

namespace App\Filament\Resources\CandidateResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementSDM\Applications;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ApplicationRelationManager extends RelationManager
{
    protected static string $relationship = 'Application';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->label('Cabang')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('recruitment_id')
                            ->relationship('recruitment', 'job_title')
                            ->label('Lowongan')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('candidate_id')
                            ->relationship('candidate', 'first_name')
                            ->label('Kandidat')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\FileUpload::make('resume')
                            ->label('Resume')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(500000)
                            ->directory('resumes')
                            ->nullable()
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Tanggal Lamaran')
                            ->required()
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->required()
                            ->default(now()),
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
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recruitment.job_title')
                    ->label('Lowongan')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-briefcase')
                    ->color('success'),
                Tables\Columns\TextColumn::make('candidate.first_name')
                    ->label('Kandidat')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-user')
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'applied',
                        'success' => 'hired',
                        'primary' => ['review', 'interview'],
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'applied' => 'Melamar',
                        'review' => 'Ditinjau',
                        'interview' => 'Wawancara',
                        'hired' => 'Diterima',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('resume')
                    ->label('Resume')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-m-document'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Lamaran')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-m-calendar'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-m-clock')
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Cabang'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'applied' => 'Applied',
                        'review' => 'Review',
                        'interview' => 'Interview',
                        'hired' => 'Hired',
                        'rejected' => 'Rejected',
                    ])
                    ->multiple()
                    ->label('Application Status'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('view_resume')
                        ->label('Lihat Resume')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn(Applications $record) => $record->resume ? Storage::url($record->resume) : '#')
                        ->openUrlInNewTab()
                        ->modalWidth('lg'),
                ])
            ])
            ->headerActions([
                // CreateAction::make()->icon('heroicon-o-plus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make()
                //     ->icon('heroicon-o-plus')
            ]);
    }
}
