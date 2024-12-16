<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use App\Models\ManagementSDM\Attendance;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AttendanceRelationManager extends RelationManager
{
    protected static string $relationship = 'attendance';

    protected static ?string $title = 'Kehadiran';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Attendance Details')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->label('Cabang')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pengguna')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('schedule_id')
                            ->relationship('schedule', 'date')
                            ->label('Jadwal')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->native(false),
                        Forms\Components\TimePicker::make('schedules_check_in')
                            ->label('Jadwal Masuk')
                            ->native(false)
                            ->seconds(false),
                        Forms\Components\TimePicker::make('schedules_check_out')
                            ->label('Jadwal Keluar')
                            ->native(false)
                            ->seconds(false),
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
                            ->native(false)
                            ->seconds(false),
                        Forms\Components\TimePicker::make('check_out')
                            ->label('Waktu Keluar')
                            ->native(false)
                            ->seconds(false),
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'present' => 'Hadir',
                                'absent' => 'Tidak Hadir',
                                'late' => 'Terlambat',
                                'on_leave' => 'Cuti',
                            ])
                            ->required()
                            ->default('present')
                            ->native(false),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->maxLength(255)
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
                    ->collapsible()
                    ->collapsed()
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable()
                    ->iconColor('primary'),
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label('Karyawan')
                    ->icon('heroicon-m-user')
                    ->iconColor('success'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->toggleable()
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->iconColor('warning'),
                Tables\Columns\TextColumn::make('is_late')
                    ->label('Keterangan')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return $record->isLate() ? 'Terlambat' : 'Tepat Waktu';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Tepat Waktu' => 'success',
                        'Terlambat' => 'danger',
                    })
                    ->description(fn(Attendance $record): string => 'Durasi : ' . $record->workDuration()),
                Tables\Columns\TextColumn::make('schedules_check_in')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Jadwal Masuk')
                    ->icon('heroicon-m-clock')
                    ->iconColor('primary'),
                Tables\Columns\TextColumn::make('schedules_check_out')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Jadwal Keluar')
                    ->icon('heroicon-m-clock')
                    ->iconColor('primary'),
                Tables\Columns\TextColumn::make('check_in')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Waktu Masuk')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->iconColor('success'),
                Tables\Columns\TextColumn::make('check_out')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Waktu Keluar')
                    ->icon('heroicon-m-arrow-left-circle')
                    ->iconColor('danger'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->label('Status')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'secondary' => 'on_leave',
                    ]),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('schedule.date')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label('Jadwal')
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d F Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d F Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])->defaultSort('created_at', 'desc')
            ->filters([
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
                Tables\Filters\Filter::make('check_in')
                    ->form([
                        Forms\Components\TimePicker::make('check_in_from')
                            ->label('Waktu Masuk Dari'),
                        Forms\Components\TimePicker::make('check_in_until')
                            ->label('Waktu Masuk Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['check_in_from'],
                                fn(Builder $query, $date): Builder => $query->whereTime('check_in', '>=', $date),
                            )
                            ->when(
                                $data['check_in_until'],
                                fn(Builder $query, $date): Builder => $query->whereTime('check_in', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['check_in_from'] ?? null) {
                            $indicators['check_in_from'] = 'Waktu masuk dari ' . $data['check_in_from'];
                        }
                        if ($data['check_in_until'] ?? null) {
                            $indicators['check_in_until'] = 'Waktu masuk sampai ' . $data['check_in_until'];
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\Filter::make('check_out')
                    ->form([
                        Forms\Components\TimePicker::make('check_out_from')
                            ->label('Waktu Keluar Dari'),
                        Forms\Components\TimePicker::make('check_out_until')
                            ->label('Waktu Keluar Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['check_out_from'],
                                fn(Builder $query, $date): Builder => $query->whereTime('check_out', '>=', $date),
                            )
                            ->when(
                                $data['check_out_until'],
                                fn(Builder $query, $date): Builder => $query->whereTime('check_out', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['check_out_from'] ?? null) {
                            $indicators['check_out_from'] = 'Waktu keluar dari ' . $data['check_out_from'];
                        }
                        if ($data['check_out_until'] ?? null) {
                            $indicators['check_out_until'] = 'Waktu keluar sampai ' . $data['check_out_until'];
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_note')
                    ->label('Catatan')
                    ->placeholder('Semua')
                    ->trueLabel('Dengan Catatan')
                    ->falseLabel('Tanpa Catatan')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('note'),
                        false: fn(Builder $query) => $query->whereNull('note'),
                    ),
            ])
            ->actions([
                tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('check_in')
                        ->icon('letsicon-in')
                        ->color('success')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\DatePicker::make('date')
                                ->default(now())
                                ->required(),
                            Forms\Components\TimePicker::make('check_in')
                                ->default(now())
                                ->required(),
                            Forms\Components\Select::make('status')
                                ->options([
                                    'present' => 'Hadir',
                                    'late' => 'Terlambat',
                                ])
                                ->default('present')
                                ->required(),
                            Forms\Components\Textarea::make('note')
                                ->label('Catatan')
                                ->rows(3),
                        ])
                        ->action(function (array $data, Attendance $record): void {
                            $record->update($data);
                            Notification::make()
                                ->title('Berhasil Melakukan Check In')
                                ->success()
                                ->send();
                        })
                        ->visible(fn(Attendance $record): bool => $record->check_in === null),
                    Tables\Actions\Action::make('check_out')
                        ->icon('letsicon-out')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\TimePicker::make('check_out')
                                ->default(now())
                                ->required(),
                            Forms\Components\Textarea::make('note')
                                ->label('Catatan')
                                ->rows(3),
                        ])
                        ->action(function (array $data, Attendance $record): void {
                            $record->update($data);
                            Notification::make()
                                ->title('Berhasil Melakukan Check Out')
                                ->success()
                                ->send();
                        })
                        ->visible(fn(Attendance $record): bool => $record->check_in !== null && $record->check_out === null),
                ])
            ])
            ->headerActions([
                // CreateAction::make()->icon('heroicon-o-plus')->label('Buat Absensi Baru'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\ForceDeleteBulkAction::make(),
                //     Tables\Actions\RestoreBulkAction::make(),
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateActions([
                // CreateAction::make()
                //     ->icon('heroicon-o-plus')->label('Buat Absensi Baru')
            ]);
    }
}
