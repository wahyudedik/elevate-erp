<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Branch;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Components\Actions;
use App\Filament\Exports\BranchExporter;
use App\Filament\Imports\BranchImporter;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use Humaidem\FilamentMapPicker\Fields\OSMMap;
use App\Filament\Resources\BranchResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BranchResource\RelationManagers;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationLabel = 'Cabang';

    protected static ?string $modelLabel = 'Cabang';

    protected static ?string $pluralModelLabel = 'Cabang';

    protected static ?string $slug = 'cabang';

    protected static ?int $navigationSort = 4;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'branches';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Cabang')
                    ->description('Lengkapi informasi detail cabang perusahaan')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Cabang')
                            ->required()
                            ->placeholder('Contoh: Cabang Pusat Jakarta')
                            ->columnSpanFull()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('address')
                            ->label('Alamat Lengkap')
                            ->placeholder('Masukkan alamat lengkap cabang')
                            ->columnSpanFull()
                            ->maxLength(255)
                            ->helperText('Sertakan nama jalan, nomor, kota, dan kode pos'),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->placeholder('Contoh: 81234567890')
                            ->prefix('+62')
                            ->helperText('Masukkan nomor tanpa tanda atau spasi')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->placeholder('Contoh: cabang.jakarta@perusahaan.com')
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi Cabang')
                            ->placeholder('Jelaskan informasi penting tentang cabang')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->helperText('Tambahkan informasi seperti jam operasional atau layanan khusus'),
                        Forms\Components\Select::make('status')
                            ->label('Status Operasional')
                            ->options([
                                'active' => 'Aktif Beroperasi',
                                'inactive' => 'Tidak Beroperasi',
                            ])
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Lokasi Geografis')
                    ->description('Tentukan lokasi dan area jangkauan cabang')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Garis Lintang (Latitude)')
                            ->live(onBlur: true)
                            ->step(0.000001),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Garis Bujur (Longitude)')
                            ->live(onBlur: true)
                            ->step(0.000001),
                        Forms\Components\TextInput::make('radius')
                            ->label('Radius Jangkauan')
                            ->numeric()
                            ->step(1)
                            ->suffix('meter')
                            ->helperText('Tentukan radius area operasional dalam satuan meter'),
                        OSMMap::make('location')
                            ->label('Peta Lokasi Cabang')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set, $record) {
                                if ($record) {
                                    $latitude = $record->latitude;
                                    $longitude = $record->longitude;

                                    if ($latitude && $longitude) {
                                        $set('location', ['lat' => $latitude, 'lng' => $longitude]);
                                    }
                                }
                            })
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $set('latitude', $state['lat']);
                                $set('longitude', $state['lng']);
                            })
                            ->showMarker()
                            ->draggable()
                            ->extraControl([
                                'zoomDelta' => 1,
                                'zoomSnap' => 0.25,
                                'wheelPxPerZoomLevel' => 60,
                                'locate' => [
                                    'enableHighAccuracy' => true,
                                    'maximumAge' => 0,
                                    'timeout' => 5000,
                                    'setView' => 'always',
                                    'maxZoom' => 18,
                                    'watch' => true,
                                    'clickBehavior' => [
                                        'inView' => 'stop',
                                        'outOfView' => 'setView',
                                        'inViewNotFollowing' => 'setView'
                                    ]
                                ]
                            ])
                            ->tilesUrl('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Riwayat Data')
                    ->description('Informasi waktu pembuatan dan pembaruan data')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Tanggal Pembuatan')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Pembaruan Terakhir')
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
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Cabang')
                    ->icon('heroicon-o-building-office-2')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->color('success'),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat Lengkap')
                    ->sortable()
                    ->html()
                    ->icon('heroicon-o-map-pin')
                    ->wrap()
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Nomor Telepon')
                    ->sortable()
                    ->icon('heroicon-o-device-phone-mobile')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->color('info')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Alamat Email')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->color('info')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(50)
                    ->html()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('latitude')
                    ->label('Koordinat Lintang')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-map')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('longitude')
                    ->label('Koordinat Bujur')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-map')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('radius')
                    ->label('Radius Area')
                    ->sortable()
                    ->suffix(' meter')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-arrows-pointing-out')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status Operasional')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'secondary',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-badge',
                        'inactive' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn(string $state): string => $state === 'active' ? 'Beroperasi' : 'Tidak Beroperasi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pembuatan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-clock')
                    ->size('sm'),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Status Terhapus')
                    ->indicator('Terhapus'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ])
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->indicator('Status'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dibuat dari tanggal')
                            ->placeholder('Pilih tanggal')
                            ->native(false),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Dibuat sampai tanggal')
                            ->placeholder('Pilih tanggal')
                            ->native(false),
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
                    ->columns(2)
                    ->label('Filter Tanggal')
                    ->indicator('Tanggal')
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Ubah')
                        ->icon('heroicon-o-pencil')
                        ->color('warning'),
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Hapus Permanen')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Pulihkan')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                ])->tooltip('Aksi')
                    ->color('gray')
                    ->icon('heroicon-m-ellipsis-horizontal')
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Cabang')
                    ->icon('heroicon-m-plus')
                    ->color('primary'),
                ActionGroup::make([
                    ExportAction::make()->exporter(BranchExporter::class)
                        ->label('Ekspor Data')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor data cabang selesai pada' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(BranchImporter::class)
                        ->label('Impor Data')
                        ->icon('heroicon-m-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Impor data cabang selesai pada' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->label('Lainnya')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->tooltip('Opsi Lainnya')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-m-trash')
                        ->color('danger'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->icon('heroicon-m-trash')
                        ->color('danger'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan')
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('success'),
                    ExportBulkAction::make()->exporter(BranchExporter::class)
                        ->label('Ekspor Data')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor data cabang selesai pada' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->tooltip('Aksi Massal')
                    ->color('gray'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Cabang')
                    ->icon('heroicon-m-plus')
                    ->color('primary'),
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
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
            'name',
            'address',
            'phone',
            'email',
            'description',
            'latitude',
            'longitude',
            'radius',
            'status',
        ];
    }
}
