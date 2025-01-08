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
                Forms\Components\Section::make('User Information')
                    ->description('Manage user profile information and settings')
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
                            ->label('Profile Image')
                            ->helperText('Upload a profile picture (max 5MB)'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-user')
                            ->placeholder('Enter full name'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-envelope')
                            ->placeholder('Enter email address'),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->prefixIcon('heroicon-o-calendar')
                            ->native(false),
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->prefixIcon('heroicon-o-user-group')
                            ->placeholder('Select user role'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->prefixIcon('heroicon-o-key')
                            ->revealable()
                            ->placeholder('Enter password'),
                        Forms\Components\Select::make('usertype')
                            ->options([
                                'staff' => 'Staff',
                                'member' => 'Member',
                            ])
                            ->required()
                            ->default('staff')
                            ->prefixIcon('heroicon-o-identification')
                            ->placeholder('Select user type'),
                    ])->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->description('System generated information')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            // ->icon('heroicon-o-clock')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            // ->icon('heroicon-o-arrow-path')
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
                    ->label('Nama')
                    ->toggleable()
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->weight('medium')
                    ->copyable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->toggleable()
                    ->searchable()
                    ->copyable()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Verifikasi Email')
                    ->icon('heroicon-o-check-badge')
                    ->toggleable()
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('usertype')
                    ->label('Tipe Pengguna')
                    ->toggleable()
                    ->badge()
                    ->icon('heroicon-o-identification')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Peran')
                    ->icon('heroicon-o-user-group')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-clock')
                    ->since(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
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
