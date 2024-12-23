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
                Forms\Components\Section::make('Informasi Pribadi')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->label('Cabang')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nama Depan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nama Belakang')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Tanggal Lahir'),
                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                                'other' => 'Lainnya',
                            ]),
                        Forms\Components\TextInput::make('national_id_number')
                            ->label('Nomor KTP')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Lamaran')
                    ->schema([
                        Forms\Components\Select::make('position_applied')
                            ->label('Posisi yang Dilamar')
                            ->required()
                            ->options(function () {
                                return Recruitment::where('status', 'open')->pluck('job_title', 'id');
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('recruiter_id')
                            ->label('Perekrut')
                            ->relationship('recruiter', 'first_name')
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                $employee = \App\Models\ManagementSDM\Employee::find($state);
                                $user = $employee?->user;

                                if ($record && $user) {
                                    Notification::make()
                                        ->title('Assigned New Candidate')
                                        ->body('You have been assigned a new candidate')
                                        ->icon('heroicon-o-user-group')
                                        ->actions([
                                            \Filament\Notifications\Actions\Action::make('view')
                                                ->button()
                                                ->url(CandidateResource::getUrl('edit', ['record' => $record]))
                                                ->openUrlInNewTab()
                                        ])
                                        ->success()
                                        ->sendToDatabase($user);
                                }
                            }),
                        Forms\Components\DatePicker::make('application_date')
                            ->label('Tanggal Lamaran')
                            ->required()
                            ->default(now()),
                        Forms\Components\FileUpload::make('resume')
                            ->label('Resume')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->directory('candidate-resumes')
                            ->downloadable()
                            ->openable(),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Alamat')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Alamat')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->label('Kota')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->label('Provinsi')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Kode Pos')
                            ->maxLength(255),
                        Forms\Components\Select::make('country')
                            ->label('Negara')
                            ->searchable()
                            ->options([
                                'Indonesia' => 'Indonesia'
                            ]),
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
                    ->label('Cabang')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nama Depan')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-user')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nama Belakang')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->icon('heroicon-o-envelope')
                    ->copyable()
                    ->copyMessage('Email berhasil disalin')
                    ->color('success'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->icon('heroicon-o-phone')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('Nomor telepon berhasil disalin')
                    ->color('success'),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->toggleable()
                    ->icon('heroicon-o-calendar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'primary' => 'male',
                        'pink-500' => 'female',
                        'warning' => 'other',
                    ])
                    ->icon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('national_id_number')
                    ->label('Nomor KTP')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-identification')
                    ->copyable()
                    ->copyMessage('Nomor KTP berhasil disalin'),
                Tables\Columns\TextColumn::make('position_applied')
                    ->label('Posisi yang Dilamar')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-briefcase')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->size('lg')
                    ->toggleable()
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'applied',
                        'primary' => fn($state) => in_array($state, ['interviewing', 'offered']),
                        'success' => 'hired',
                    ])
                    ->icon('heroicon-o-user-circle'),
                Tables\Columns\TextColumn::make('recruiter.first_name')
                    ->label('Perekrut')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-user-group')
                    ->sortable(),
                Tables\Columns\TextColumn::make('application_date')
                    ->label('Tanggal Lamaran')
                    ->date('d M Y')
                    ->toggleable()
                    ->icon('heroicon-o-calendar-days')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Kota')
                    ->toggleable()
                    ->icon('heroicon-o-map-pin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->label('Negara')
                    ->toggleable()
                    ->icon('heroicon-o-globe-alt')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-arrow-path')
                    ->toggleable(isToggledHiddenByDefault: true)
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Cabang'),
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        'other' => 'Lainnya',
                    ])
                    ->label('Jenis Kelamin')
                    ->placeholder('Semua Jenis Kelamin'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'applied' => 'Melamar',
                        'interviewing' => 'Wawancara',
                        'offered' => 'Ditawari',
                        'hired' => 'Diterima',
                        'rejected' => 'Ditolak',
                    ])
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->multiple(),
                Tables\Filters\Filter::make('application_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
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
                            $indicators['from'] = 'Lamaran dari ' . Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Lamaran sampai ' . Carbon::parse($data['until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\SelectFilter::make('recruiter')
                    ->relationship('recruiter', 'first_name')
                    ->label('Perekrut')
                    ->placeholder('Semua Perekrut')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('has_resume')
                    ->label('Memiliki Resume')
                    ->placeholder('Semua Kandidat')
                    ->trueLabel('Dengan Resume')
                    ->falseLabel('Tanpa Resume')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('resume'),
                        false: fn(Builder $query) => $query->whereNull('resume'),
                    ),
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
                        ->label('Ubah Status')
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

                            // The observer will handle interview creation automatically

                            // Update related applications
                            Applications::where('candidate_id', $record->id)
                                ->update(['status' => $data['status']]);

                            Notification::make()
                                ->title('Status updated successfully')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])

            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus')->label('Buat Kandidat Baru'),
                ActionGroup::make([
                    ExportAction::make()->exporter(CandidateExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor kandidat selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(CandidateImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Impor kandidat selesai' . ' ' . now())
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
                    ->label('Ekspor')
                    ->after(function () {
                        Notification::make()
                            ->title('Ekspor kandidat selesai' . ' ' . now())
                            ->success()
                            ->sendToDatabase(Auth::user());
                    }),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Buat Kandidat Baru'),
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
