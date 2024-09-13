<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementSDM\Applications;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\ApplicationsExporter;
use App\Filament\Imports\ApplicationsImporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ApplicationsResource\Pages;
use App\Filament\Resources\ApplicationsResource\RelationManagers;

class ApplicationsResource extends Resource
{
    protected static ?string $model = Applications::class;

    protected static ?string $navigationBadgeTooltip = 'Total Applications Candidates';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationParentItem = 'Recruitment Management';

    protected static ?string $navigationGroup = 'Management SDM';

    protected static ?string $navigationIcon = 'fileicon-docz';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('recruitment_id')
                            ->relationship('recruitment', 'job_title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('candidate_id')
                            ->relationship('candidate', 'first_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\FileUpload::make('resume')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(500000)
                            ->directory('resumes')
                            ->nullable()
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Application Date')
                            ->required()
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Last Updated')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('recruitment.job_title')
                    ->label('Recruitment')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('candidate.first_name')
                    ->label('Candidate')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'applied',
                        'success' => 'hired',
                        'primary' => ['review', 'interview'],
                    ]),
                Tables\Columns\TextColumn::make('resume')
                    ->label('Resume')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Application Date')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
                Tables\Filters\SelectFilter::make('recruitment')
                    ->relationship('recruitment', 'job_title')
                    ->label('Recruitment'),
                Tables\Filters\SelectFilter::make('candidate')
                    ->relationship('candidate', 'first_name')
                    ->label('Candidate'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Applied From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Applied Until'),
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Applied from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Applied until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('view_resume')
                        ->label('View Resume')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn(Applications $record) => $record->resume ? Storage::url($record->resume) : '#')
                        ->openUrlInNewTab()
                        ->modalWidth('lg'),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(ApplicationsExporter::class)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->after(function () {
                        Notification::make()
                            ->title('Candidates exported successfully')
                            ->success()
                            ->sendToDatabase(Auth::user());
                    }),
                ImportAction::make()
                    ->importer(ApplicationsImporter::class)
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->after(function () {
                        Notification::make()
                            ->title('Candidates imported successfully')
                            ->success()
                            ->sendToDatabase(Auth::user());
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                ExportBulkAction::make()
                    ->exporter(ApplicationsExporter::class)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->after(function () {
                        Notification::make()
                            ->title('Data exported successfully')
                            ->success()
                            ->sendToDatabase(Auth::user());
                    }),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
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
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplications::route('/create'),
            'edit' => Pages\EditApplications::route('/{record}/edit'),
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
