<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Auth\Middleware\Authenticate;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?string $tenantRelationshipName = 'members';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->description('Kelola informasi profil dan pengaturan pengguna')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('user-images')
                            ->visibility('public')
                            ->maxSize(5024)
                            ->imageEditor()
                            ->columnSpanFull()
                            ->label('Foto Profil')
                            ->helperText('Unggah foto profil (maksimal 5MB)')
                            ->imagePreviewHeight('250')
                            ->panelAspectRatio('2:1'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-user')
                            ->placeholder('Masukkan nama lengkap')
                            ->label('Nama Lengkap'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-envelope')
                            ->placeholder('Masukkan alamat email')
                            ->label('Alamat Email'),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->prefixIcon('heroicon-o-calendar')
                            ->native(false)
                            ->label('Tanggal Verifikasi Email'),
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->prefixIcon('heroicon-o-user-group')
                            ->placeholder('Pilih peran pengguna')
                            ->label('Peran')
                            ->searchPrompt('Cari peran pengguna')
                            ->noSearchResultsMessage('Peran tidak ditemukan'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->prefixIcon('heroicon-o-key')
                            ->revealable()
                            ->placeholder('Masukkan kata sandi')
                            ->label('Kata Sandi'),
                        Forms\Components\Select::make('usertype')
                            ->options([
                                'staff' => 'Staf',
                                'member' => 'Anggota',
                            ])
                            ->required()
                            ->default('staff')
                            ->prefixIcon('heroicon-o-identification')
                            ->placeholder('Pilih tipe pengguna')
                            ->label('Tipe Pengguna'),
                    ])->columns(2),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->description('Informasi yang dihasilkan oleh sistem')
                    ->icon('heroicon-o-information-circle')
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
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->size('sm')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->toggleable()
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->weight('medium')
                    ->copyable()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Alamat Email')
                    ->icon('heroicon-o-envelope')
                    ->toggleable()
                    ->searchable()
                    ->copyable()
                    ->color('info'),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Status Verifikasi')
                    ->icon('heroicon-o-shield-check')
                    ->toggleable()
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('usertype')
                    ->label('Kategori Pengguna')
                    ->toggleable()
                    ->badge()
                    ->icon('heroicon-o-identification')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Hak Akses')
                    ->icon('heroicon-o-user-group')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pembuatan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar')
                    ->since(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-arrow-path')
                    ->since(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Verifikasi Email')
                    ->placeholder('Semua Pengguna')
                    ->trueLabel('Pengguna Terverifikasi')
                    ->falseLabel('Pengguna Belum Terverifikasi')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn(Builder $query) => $query->whereNull('email_verified_at'),
                    ),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
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
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('success')
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->color('info')
                        ->icon('heroicon-o-pencil-square'),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger')
                        ->icon('heroicon-o-trash'),
                    ActivityLogTimelineTableAction::make('Activities')
                        ->icon('heroicon-o-clock'),
                ])->tooltip('Actions')
                    ->icon('heroicon-m-ellipsis-horizontal')
            ])
            ->headerActions([
                ActionGroup::make([
                    ExportAction::make()
                        ->label('Ekspor Data')
                        ->exporter(UserExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->tooltip('Ekspor data pengguna ke Excel')
                        ->after(function () {
                            Notification::make()
                                ->title('Data pengguna berhasil diekspor' . ' ' . now()->format('d/m/Y H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->label('Impor Data')
                        ->importer(UserImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('warning')
                        ->tooltip('Impor data pengguna dari Excel')
                        ->after(function () {
                            Notification::make()
                                ->title('Data pengguna berhasil diimpor' . ' ' . now()->format('d/m/Y H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                ])->icon('heroicon-o-cog-6-tooth')
                    ->label('Pengaturan')
                    ->tooltip('Menu pengaturan')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->tooltip('Hapus data yang dipilih'),
                    ExportBulkAction::make()
                        ->label('Ekspor Terpilih')
                        ->exporter(UserExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->tooltip('Ekspor data yang dipilih ke Excel')
                        ->after(function () {
                            Notification::make()
                                ->title('Data pengguna berhasil diekspor' . ' ' . now()->format('d/m/Y H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->tooltip('Aksi Massal'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
