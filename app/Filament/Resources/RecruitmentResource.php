<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use App\Filament\Clusters\Employee;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Collection;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Models\ManagementSDM\Recruitment;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\RecruitmentExporter;
use App\Filament\Imports\RecruitmentImporter;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\RecruitmentResource\Pages;
use App\Filament\Resources\RecruitmentResource\RelationManagers;
use App\Filament\Resources\RecruitmentResource\RelationManagers\CandidatesRelationManager;

class RecruitmentResource extends Resource
{
    protected static ?string $model = Recruitment::class;

    protected static ?string $navigationLabel = 'Rekrutmen';

    protected static ?string $modelLabel = 'Rekrutmen';
    
    protected static ?string $pluralModelLabel = 'Rekrutmen';

    protected static ?string $cluster = Employee::class;

    protected static ?int $navigationSort = 10; //29

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'recruitments';

    protected static ?string $navigationGroup = 'Recruitment Management';

    protected static ?string $navigationIcon = 'vaadin-user-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Job Details')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('job_title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('job_description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('employment_type')
                            ->required()
                            ->options([
                                'full_time' => 'Full Time',
                                'part_time' => 'Part Time',
                                'contract' => 'Contract',
                                'internship' => 'Internship',
                            ]),
                        Forms\Components\TextInput::make('location')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('posted_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('closing_date'),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'open' => 'Open',
                                'closed' => 'Closed',
                                'on_hold' => 'On Hold',
                            ])
                            ->default('open'),
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
                Tables\Columns\TextColumn::make('job_title')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('employment_type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'full_time' => 'success',
                        'part_time' => 'warning',
                        'contract' => 'danger',
                        'internship' => 'info',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->icon('heroicon-o-map-pin')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('posted_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('closing_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'danger',
                        'on_hold' => 'warning',
                    })
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
                Tables\Filters\SelectFilter::make('employment_type')
                    ->options([
                        'full_time' => 'Full Time',
                        'part_time' => 'Part Time',
                        'contract' => 'Contract',
                        'internship' => 'Internship',
                    ])
                    ->label('Employment Type')
                    ->multiple(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                        'on_hold' => 'On Hold',
                    ])
                    ->label('Status'),
                Tables\Filters\Filter::make('posted_date')
                    ->form([
                        Forms\Components\DatePicker::make('posted_from')
                            ->label('Posted From'),
                        Forms\Components\DatePicker::make('posted_until')
                            ->label('Posted Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['posted_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('posted_date', '>=', $date),
                            )
                            ->when(
                                $data['posted_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('posted_date', '<=', $date),
                            );
                    })->columns(2),
                Tables\Filters\Filter::make('closing_date')
                    ->form([
                        Forms\Components\DatePicker::make('closing_from')
                            ->label('Closing From'),
                        Forms\Components\DatePicker::make('closing_until')
                            ->label('Closing Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['closing_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('closing_date', '>=', $date),
                            )
                            ->when(
                                $data['closing_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('closing_date', '<=', $date),
                            );
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_closing_date')
                    ->label('Has Closing Date')
                    ->nullable()
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('closing_date'),
                        false: fn(Builder $query) => $query->whereNull('closing_date'),
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('close')
                        ->label('Close Recruitment')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Recruitment $record) {
                            $record->update(['status' => 'closed', 'closing_date' => now()]);
                        })
                        ->visible(fn(Recruitment $record): bool => $record->status === 'open'),
                    Tables\Actions\Action::make('reopen')
                        ->label('Reopen Recruitment')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Recruitment $record) {
                            $record->update(['status' => 'open', 'closing_date' => null]);
                        })
                        ->visible(fn(Recruitment $record): bool => $record->status === 'closed'),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('Link to Application Form')
                        ->label('Link to Application Form')
                        ->icon('heroicon-o-map')
                        ->color('primary')
                        ->url(fn(Recruitment $record): string => route('candidate.apply', $record))
                        ->openUrlInNewTab(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('close')
                        ->label('Close Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'closed', 'closing_date' => now()]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('reopen')
                        ->label('Reopen Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'open', 'closing_date' => null]);
                        })
                        ->deselectRecordsAfterCompletion(),

                    ExportBulkAction::make()->exporter(RecruitmentExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export recruitment completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()->exporter(RecruitmentExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export recruitment completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(RecruitmentImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Import recruitment completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->icon('heroicon-o-cog-6-tooth')
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
            'index' => Pages\ListRecruitments::route('/'),
            'create' => Pages\CreateRecruitment::route('/create'),
            'edit' => Pages\EditRecruitment::route('/{record}/edit'),
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
            'job_title',            // Judul pekerjaan
            'job_description',      // Deskripsi pekerjaan
            'employment_type',      // full_time, part_time, contract
            'location',             // Lokasi kerja
            'posted_date',          // Tanggal lowongan diposting
            'closing_date',         // Tanggal penutupan lowongan
            'status',  // Jumlah posisi yang dibutuhkan
        ];
    }
}
