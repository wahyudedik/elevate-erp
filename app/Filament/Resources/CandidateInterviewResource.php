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
                Forms\Components\Section::make('Informasi Wawancara Kandidat')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->label('Cabang')
                            ->searchable()
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
                        Forms\Components\Select::make('interviewer_id')
                            ->label('Pewawancara')
                            ->relationship('interviewer', 'first_name')
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                $employee = Employee::find($state);
                                $user = $employee?->user;

                                if ($record && $user) {
                                    Notification::make()
                                        ->title('Ditugaskan Kandidat Baru')
                                        ->body('Anda telah ditugaskan kandidat baru')
                                        ->icon('heroicon-o-user-group')
                                        ->actions([
                                            \Filament\Notifications\Actions\Action::make('view')
                                                ->button()
                                                ->url(CandidateInterviewResource::getUrl('edit', ['record' => $record]))
                                                ->openUrlInNewTab()
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
                            ->label('Jenis Wawancara')
                            ->required()
                            ->default('in_person'),
                        Forms\Components\Textarea::make('interview_notes')
                            ->label('Catatan Wawancara')
                            ->placeholder('Masukkan catatan wawancara di sini')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('result')
                            ->options([
                                'passed' => 'Lulus',
                                'failed' => 'Gagal',
                                'pending' => 'Tertunda',
                            ])
                            ->required()
                            ->default('pending')
                            ->label('Hasil Wawancara'),
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

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Columns\TextColumn::make('candidate.first_name')
                    ->label('Kandidat')
                    ->toggleable()
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->sortable(),
                Tables\Columns\TextColumn::make('interview_date')
                    ->label('Tanggal Wawancara')
                    ->toggleable()
                    ->icon('heroicon-o-calendar-days')
                    ->date('d M Y')
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('interviewer.first_name')
                    ->label('Pewawancara')
                    ->searchable()
                    ->icon('heroicon-o-user-circle')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('interview_type')
                    ->badge()
                    ->label('Jenis Wawancara')
                    ->toggleable()
                    ->colors([
                        'primary' => 'phone',
                        'success' => 'video',
                        'warning' => 'in_person',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'phone' => 'Telepon',
                        'video' => 'Video',
                        'in_person' => 'Tatap Muka',
                    }),
                Tables\Columns\TextColumn::make('interview_notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->icon('heroicon-o-clipboard-document-list')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('result')
                    ->badge()
                    ->label('Hasil')
                    ->toggleable()
                    ->colors([
                        'success' => 'passed',
                        'danger' => 'failed',
                        'warning' => 'pending',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'passed' => 'Lulus',
                        'failed' => 'Gagal',
                        'pending' => 'Tertunda',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-arrow-path')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Cabang'),
                Tables\Filters\SelectFilter::make('interview_type')
                    ->options([
                        'phone' => 'Telepon',
                        'video' => 'Video',
                        'in_person' => 'Tatap Muka',
                    ])
                    ->label('Jenis Wawancara')
                    ->multiple(),
                Tables\Filters\SelectFilter::make('result')
                    ->options([
                        'passed' => 'Lulus',
                        'failed' => 'Gagal',
                        'pending' => 'Tertunda',
                    ])
                    ->label('Hasil')
                    ->multiple(),
                Tables\Filters\Filter::make('interview_date')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('Sampai Tanggal'),
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
                    ->label('Ada Catatan')
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
                        ->label('Ubah Hasil')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('result')
                                ->label('Hasil')
                                ->options([
                                    'passed' => 'Lulus',
                                    'failed' => 'Gagal',
                                    'pending' => 'Tertunda',
                                ])
                                ->required(),
                        ])
                        ->action(function (CandidateInterview $record, array $data) {
                            $record->update(['result' => $data['result']]);
                            Notification::make()
                                ->title('Hasil wawancara berhasil diperbarui')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('addNotes')
                        ->label('Tambah Catatan')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->form([
                            Forms\Components\Textarea::make('interview_notes')
                                ->label('Catatan Wawancara')
                                ->required(),
                        ])
                        ->action(function (CandidateInterview $record, array $data) {
                            $record->update(['interview_notes' => $data['interview_notes']]);
                            Notification::make()
                                ->title('Catatan wawancara berhasil ditambahkan')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('reschedule')
                        ->label('Jadwal Ulang')
                        ->icon('heroicon-o-calendar')
                        ->color('primary')
                        ->form([
                            Forms\Components\DatePicker::make('interview_date')
                                ->label('Tanggal Wawancara Baru')
                                ->required(),
                        ])
                        ->action(function (CandidateInterview $record, array $data) {
                            $record->update(['interview_date' => $data['interview_date']]);
                            Notification::make()
                                ->title('Jadwal wawancara berhasil diubah')
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus')->label('Buat Wawancara Baru'),
                ActionGroup::make([
                    ExportAction::make()->exporter(CandidateInterviewExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor data selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(CandidateInterviewImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Impor data selesai' . ' ' . now())
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
                                ->title('Ekspor data selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Buat Wawancara Baru'),
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
            'interviewer_id',
            'interview_type',  // phone, video, in_person
            'interview_notes',
            'result',  // passed, failed, pending 
        ];
    }
}
