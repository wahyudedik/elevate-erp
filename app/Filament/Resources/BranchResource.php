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
                    ->description('Masukkan informasi detail cabang')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Cabang')
                            ->required()
                            ->placeholder('Masukkan nama cabang')
                            ->columnSpanFull()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('address')
                            ->label('Alamat')
                            ->placeholder('Masukkan alamat lengkap')
                            ->columnSpanFull()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->placeholder('81234567890')
                            ->prefix('+62')
                            ->helperText('Contoh: 81234567890')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->placeholder('contoh@email.com')
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Masukkan deskripsi cabang')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Tidak Aktif',
                            ])
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Lokasi')
                    ->description('Atur lokasi dan radius cabang')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Garis Lintang')
                            ->live(onBlur: true)
                            ->step(0.000001),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Garis Bujur')
                            ->live(onBlur: true)
                            ->step(0.000001),
                        Forms\Components\TextInput::make('radius')
                            ->label('Radius')
                            ->numeric()
                            ->step(1)
                            ->suffix('meter')
                            ->helperText('Masukkan radius dalam meter'),
                        OSMMap::make('location')
                            ->label('Peta Lokasi')
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
                Forms\Components\Section::make('Informasi Tambahan')
                    ->description('Informasi waktu pembuatan dan perubahan terakhir')
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
                    ->icon('heroicon-o-building-storefront')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->sortable()
                    ->html()
                    ->icon('heroicon-o-globe-americas')
                    ->wrap()
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->sortable()
                    ->icon('heroicon-o-phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->color('primary')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->color('primary')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->html()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('latitude')
                    ->label('Garis Lintang')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('longitude')
                    ->label('Garis Bujur')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('radius')
                    ->label('Radius')
                    ->sortable()
                    ->suffix(' meter')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'secondary',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'inactive' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn(string $state): string => $state === 'active' ? 'Aktif' : 'Tidak Aktif')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
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
