<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagementSDM\Schedule;
use App\Models\ManagementSDM\Attendance;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\AttendanceExporter;
use App\Filament\Imports\AttendanceImporter;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Filament\Resources\AttendanceResource\RelationManagers\EmployeeRelationManager;
use App\Filament\Resources\AttendanceResource\RelationManagers\AttendanceRelationManager;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationLabel = 'Absensi';

    protected static ?string $modelLabel = 'Absensi';

    protected static ?string $pluralModelLabel = 'Absensi';

    protected static ?string $cluster = Employee::class;

    protected static ?int $navigationSort = 5;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'attendance';

    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?string $navigationIcon = 'iconpark-checkin-o';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Attendance Details')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->label('Cabang')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pengguna')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->label('Karyawan')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('schedule_id')
                            ->relationship('schedule', 'date')
                            ->label('Jadwal')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->date()
                            ->required(),
                        Forms\Components\TimePicker::make('schedules_check_in')
                            ->label('Jadwal Masuk')
                            ->nullable(),
                        Forms\Components\TimePicker::make('schedules_check_out')
                            ->label('Jadwal Keluar')
                            ->nullable(),
                        Forms\Components\TextInput::make('schedules_latitude')
                            ->label('Latitude Jadwal')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('schedules_longitude')
                            ->label('Longitude Jadwal')
                            ->numeric()
                            ->required(),
                        Forms\Components\TimePicker::make('check_in')
                            ->label('Waktu Masuk')
                            ->nullable(),
                        Forms\Components\TimePicker::make('check_out')
                            ->label('Waktu Keluar')
                            ->nullable(),
                        Forms\Components\TextInput::make('latitude_check_in')
                            ->label('Latitude Check In')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('longitude_check_in')
                            ->label('Longitude Check In')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('latitude_check_out')
                            ->label('Latitude Check Out')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('longitude_check_out')
                            ->label('Longitude Check Out')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'present' => 'Hadir',
                                'absent' => 'Tidak Hadir',
                                'late' => 'Terlambat',
                                'on_leave' => 'Cuti',
                            ])
                            ->required()
                            ->default('present'),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat pada')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir diubah')
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
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label('Karyawan')
                    ->icon('heroicon-m-user')
                    ->iconColor('success')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('date')
                    ->date('Y-m-d')
                    ->toggleable()
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->label('Tanggal')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('is_late')
                    ->label('Keterangan')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $late = $record->isLate();
                        return $late['status'] ? 'Terlambat' : 'Tepat Waktu';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Tepat Waktu' => 'success',
                        'Terlambat' => 'danger',
                    })
                    ->description(function ($record) {
                        $late = $record->isLate();
                        if ($late['status']) {
                            return 'Terlambat: ' . $late['duration'];
                        }
                        return $record->check_in && $record->check_out ?
                            'Durasi Kerja: ' . $record->workDuration() :
                            'Belum Check Out';
                    })
                    ->size('sm'),
                Tables\Columns\TextColumn::make('schedules_check_in')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Jadwal Masuk')
                    ->icon('heroicon-m-clock')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('schedules_check_out')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Jadwal Pulang')
                    ->icon('heroicon-m-clock')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('check_in')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Waktu Masuk')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->iconColor('success')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('check_out')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Waktu Pulang')
                    ->icon('heroicon-m-arrow-left-circle')
                    ->iconColor('danger')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->label('Status')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'secondary' => 'on_leave',
                    ])
                    ->size('sm'),
                Tables\Columns\TextColumn::make('note')
                    ->limit(30)
                    ->toggleable()
                    ->label('Catatan')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->icon('heroicon-m-document-text')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('schedule.date')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label('Jadwal')
                    ->icon('heroicon-m-calendar-days')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dibuat Pada')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Terakhir Diubah')
                    ->size('sm')
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Cabang'),
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload()
                    ->label('Karyawan'),
                Tables\Filters\Filter::make('date')
                    ->label('Tanggal')
                    ->form([
                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '=', $date)
                            );
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'present' => 'Hadir',
                        'absent' => 'Tidak Hadir',
                        'late' => 'Terlambat',
                        'on_leave' => 'Cuti',
                    ])
                    ->multiple()
                    ->label('Status'),
            ])
            ->actions([
                tables\Actions\ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make(),
                ])
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus')->label('Buat Absensi Baru'),
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(AttendanceExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Absensi berhasil diekspor.' . ' ' . now()->format('Y-m-d H:i:s'))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(AttendanceImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Absensi berhasil diimpor.' .  ' ' . now()->format('Y-m-d H:i:s'))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->icon('heroicon-o-cog-6-tooth'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(AttendanceExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Absensi berhasil diekspor.' . ' ' . now()->format('Y-m-d H:i:s'))
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Buat Absensi Baru'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EmployeeRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
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
            'user_id',
            'branch_id',
            'employee_id',
            'schedule_id',
            'date',
            'schedules_check_in',
            'schedules_check_out',
            'schedules_latitude',
            'schedules_longitude',
            'check_in',
            'check_out',
            'latitude_check_in',
            'longitude_check_in',
            'latitude_check_out',
            'longitude_check_out',
            'status',
            'note',
        ];
    }
}
