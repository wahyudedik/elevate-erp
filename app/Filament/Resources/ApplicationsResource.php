<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use App\Filament\Clusters\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Actions\CreateAction;
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

    protected static ?string $navigationLabel = 'Curiculum Vitae';

    protected static ?string $modelLabel = 'Curiculum Vitae';

    protected static ?string $pluralModelLabel = 'Curiculum Vitae';

    protected static ?string $cluster = Employee::class;

    protected static ?int $navigationSort = 11; //29

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'applications';

    protected static ?string $navigationGroup = 'Recruitment Management';

    protected static ?string $navigationIcon = 'fileicon-docz';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->label('Cabang')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('recruitment_id')
                            ->relationship('recruitment', 'job_title')
                            ->label('Lowongan Kerja')
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
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat pada')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir dimodifikasi')
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
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->iconColor('primary')
                    ->sortable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('recruitment.job_title')
                    ->label('Lowongan')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->size('sm')
                    ->weight('medium')
                    ->icon('heroicon-m-briefcase')
                    ->iconColor('success'),
                Tables\Columns\TextColumn::make('candidate.first_name')
                    ->label('Kandidat')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->size('sm')
                    ->weight('medium')
                    ->icon('heroicon-m-user')
                    ->iconColor('info'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->size('sm')
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'applied',
                        'success' => 'hired',
                        'primary' => ['review', 'interview'],
                    ])
                    ->icon('heroicon-m-flag'),
                Tables\Columns\TextColumn::make('resume')
                    ->label('Resume')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->icon('heroicon-m-document')
                    ->iconColor('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Lamaran')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->icon('heroicon-m-calendar')
                    ->iconColor('warning'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->icon('heroicon-m-clock')
                    ->iconColor('secondary')
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Cabang'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'applied' => 'Diajukan',
                        'review' => 'Ditinjau',
                        'interview' => 'Wawancara',
                        'hired' => 'Diterima',
                        'rejected' => 'Ditolak',
                    ])
                    ->multiple()
                    ->label('Status Lamaran'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Tanggal Mulai'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Tanggal Akhir'),
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
                            $indicators['created_from'] = 'Diajukan dari ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Diajukan sampai ' . Carbon::parse($data['created_until'])->toFormattedDateString();
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
                        ->label('Lihat Resume')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn(Applications $record) => $record->resume ? Storage::url($record->resume) : '#')
                        ->openUrlInNewTab()
                        ->modalWidth('lg'),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus')->label('Buat Lamaran Baru'),
                ActionGroup::make([
                    ExportAction::make()->exporter(ApplicationsExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor lamaran selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(ApplicationsImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Impor lamaran selesai' . ' ' . now())
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
                    ExportBulkAction::make()->exporter(ApplicationsExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor lamaran selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Buat Lamaran Baru'),
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

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'company_id',
            'branch_id',
            'recruitment_id',
            'candidate_id',
            'status',  // applied, interviewing, offered, hired, rejected
            'resume',  // File path untuk resume/CV kandidat
        ];
    }
}
