<?php

namespace App\Filament\Resources\EmployeePositionResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\ManagementSDM\Employee;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EmployeeRelationManager extends RelationManager
{
    protected static string $relationship = 'employee';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nama Depan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nama Belakang')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('employee_code')
                            ->label('Kode Karyawan')
                            ->readOnly()
                            ->required()
                            ->default(function () {
                                return 'EMP-' . strtoupper(uniqid());
                            })
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->placeholder(('+628123456789 / 08123456789'))
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
                            ->maxLength(16),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Pekerjaan')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\Select::make('department_id')
                            ->label('Departemen')
                            ->relationship(
                                'department',
                                'name',
                                fn($query, $get) =>
                                $query->when(
                                    $get('branch_id'),
                                    fn($query, $branch_id) =>
                                    $query->where('branch_id', $branch_id)
                                )
                            )
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->hidden(fn($get) => ! $get('branch_id')),
                        Forms\Components\Select::make('position_id')
                            ->label('Jabatan')
                            ->relationship(
                                'position',
                                'name',
                                fn($query, $get) =>
                                $query->when(
                                    $get('department_id'),
                                    fn($query, $department_id) =>
                                    $query->where('department_id', $department_id)
                                )
                            )
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->hidden(fn($get) => ! $get('department_id')),
                        Forms\Components\DatePicker::make('date_of_joining')
                            ->label('Tanggal Bergabung')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('salary')
                            ->label('Gaji')
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(9999999999999.99),
                        Forms\Components\Select::make('employment_status')
                            ->label('Status Kepegawaian')
                            ->options([
                                'permanent' => 'Tetap',
                                'contract' => 'Kontrak',
                                'internship' => 'Magang',
                            ])
                            ->default('permanent'),
                        Forms\Components\Select::make('manager_id')
                            ->label('Manajer')
                            ->relationship('manager', 'first_name')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Tidak Aktif',
                                'terminated' => 'Diberhentikan',
                                'resigned' => 'Mengundurkan Diri',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pengguna')
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->avatar()
                                    ->disk('public')
                                    ->directory('user-images')
                                    ->visibility('public')
                                    ->maxSize(5024)
                                    ->columnSpanFull()
                                    ->label('Foto Profil'),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DateTimePicker::make('email_verified_at')
                                    ->label('Email Terverifikasi Pada'),
                                Forms\Components\Select::make('roles')
                                    ->label('Peran')
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->searchable(),
                                Forms\Components\TextInput::make('password')
                                    ->label('Kata Sandi')
                                    ->password()
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn(string $context): bool => $context === 'create'),
                                Forms\Components\Select::make('usertype')
                                    ->label('Tipe Pengguna')
                                    ->options([
                                        'staff' => 'Staf',
                                        'member' => 'Anggota',
                                    ])
                                    ->required()
                                    ->default('staff'),
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Alamat')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('province_id')
                            ->label('Provinsi')
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('city_id', null))
                            ->options(fn() => Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
                                ->collect()
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->preload(),
                        Forms\Components\Select::make('city_id')
                            ->label('Kota/Kabupaten')
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('district_id', null))
                            ->options(fn($get) => $get('province_id')
                                ? Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$get('province_id')}.json")
                                ->collect()
                                ->pluck('name', 'id')
                                : [])
                            ->searchable()
                            ->required()
                            ->preload(),
                        Forms\Components\Select::make('district_id')
                            ->label('Kecamatan')
                            ->live()
                            ->options(fn($get) => $get('city_id')
                                ? Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$get('city_id')}.json")
                                ->collect()
                                ->pluck('name', 'id')
                                : [])
                            ->searchable()
                            ->required()
                            ->preload(),
                        Forms\Components\TextInput::make('postal_code')
                            ->numeric()
                            ->label('Kode Pos'),
                    ])->columns(2),

                Forms\Components\Section::make('Dokumen Karyawan')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_picture')
                            ->image()
                            ->disk('public')
                            ->directory('employee-profile-pictures')
                            ->maxSize(1024)
                            ->label('Foto Profil')
                            ->imagePreviewHeight('250')
                            ->downloadable()
                            ->avatar()
                            ->openable()
                            ->visibility('public'),
                        Forms\Components\FileUpload::make('contract')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('public')
                            ->directory('employee-contracts')
                            ->maxSize(5120)
                            ->label('Kontrak Kerja')
                            ->downloadable()
                            ->openable()
                            ->visibility('public'),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('ID Pengguna')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user-circle')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('profile_picture')
                    ->label('Foto Profil')
                    ->circular()
                    ->disk('public')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nama Depan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nama Belakang')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('employee_code')
                    ->label('Kode Karyawan')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-identification')
                    ->copyable()
                    ->copyMessage('Kode karyawan disalin')
                    ->copyMessageDuration(1500)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email disalin')
                    ->copyMessageDuration(1500)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Tanggal Lahir')
                    ->date()
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->toggleable()
                    ->icon('heroicon-m-user')
                    ->colors([
                        'primary' => 'male',
                        'danger' => 'female',
                        'warning' => 'other',
                    ]),
                Tables\Columns\TextColumn::make('national_id_number')
                    ->label('NIK')
                    ->searchable()
                    ->icon('heroicon-m-identification')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('position.name')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-briefcase')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departemen')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-building-office')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date_of_joining')
                    ->label('Tanggal Bergabung')
                    ->date()
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('salary')
                    ->label('Gaji')
                    ->money('IDR')
                    ->sortable()
                    ->icon('heroicon-m-banknotes')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('employment_status')
                    ->label('Status Kepegawaian')
                    ->badge()
                    ->colors([
                        'success' => 'permanent',
                        'warning' => 'contract',
                        'danger' => 'internship',
                    ])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('manager.first_name')
                    ->label('Manajer')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user-group')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable()
                    ->icon('heroicon-m-map-pin')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('province_id')
                    ->label('Provinsi')
                    ->formatStateUsing(function ($state) {
                        return Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/province/{$state}.json")
                            ->collect()
                            ->get('name');
                    })
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-map')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city_id')
                    ->label('Kota/Kabupaten')
                    ->formatStateUsing(function ($state) {
                        return Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/regency/{$state}.json")
                            ->collect()
                            ->get('name');
                    })
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-map')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('district_id')
                    ->label('Kecamatan')
                    ->formatStateUsing(function ($state) {
                        return Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/district/{$state}.json")
                            ->collect()
                            ->get('name');
                    })
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-map')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->label('Kode Pos')
                    ->searchable()
                    ->icon('heroicon-m-map-pin')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => fn($state) => in_array($state, ['terminated', 'resigned']),
                    ])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-m-clock')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-m-clock')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([])
            ->headerActions([])
            ->actions([
                ViewAction::make('view')
            ])
            ->bulkActions([]);
    }
}
