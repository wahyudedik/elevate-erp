<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\DepartmentExporter;
use App\Filament\Imports\DepartmentImporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportBulkAction;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationLabel = 'Departemen';

    protected static ?string $modelLabel = 'Departemen';

    protected static ?string $pluralModelLabel = 'Departemen';

    protected static ?int $navigationSort = 5;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'departments';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Departemen')
                    ->description('Masukkan informasi departemen dengan lengkap')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name', fn(Builder $query) => $query->where('status', 'active'))
                            ->searchable()
                            ->required()
                            ->preload()
                            ->nullable()
                            ->helperText('Pilih cabang tempat departemen berada'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Departemen')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Masukkan nama departemen'),
                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi')
                            ->nullable()
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->helperText('Berikan deskripsi lengkap tentang departemen'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->description('Detail waktu pembuatan dan modifikasi')
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
                    ->label('No')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->size('sm')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->icon('heroicon-o-building-storefront')
                    ->sortable()
                    ->toggleable()
                    ->weight('medium')
                    ->size('sm')
                    ->color('success'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Departemen')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->weight('medium')
                    ->size('sm')
                    ->color('info'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->sortable()
                    ->html()
                    ->limit(50)
                    ->toggleable()
                    ->wrap()
                    ->size('sm')
                    ->tooltip(fn($record) => $record->description),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since()
                    ->color('gray')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since()
                    ->color('gray')
                    ->size('sm'),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Status Terhapus'),
                Tables\Filters\SelectFilter::make('branch')
                    ->label('Cabang')
                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal awal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
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
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['created_from'] && $data['created_until']) {
                            return 'Dibuat dari ' . $data['created_from'] . ' sampai ' . $data['created_until'];
                        }
                        return null;
                    }),
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
                        ->icon('heroicon-o-arrow-path')
                        ->color('success'),
                ])->tooltip('Aksi')
                    ->icon('heroicon-m-ellipsis-horizontal')
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Departemen')
                    ->icon('heroicon-m-plus')
                    ->color('primary'),
                ActionGroup::make([
                    ExportAction::make()->exporter(DepartmentExporter::class)
                        ->label('Ekspor Data')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor data departemen selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(DepartmentImporter::class)
                        ->label('Impor Data')
                        ->icon('heroicon-m-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Impor data departemen selesai' . ' ' . now())
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
                        ->icon('heroicon-m-arrow-path')
                        ->color('success'),
                    ExportBulkAction::make()->exporter(DepartmentExporter::class)
                        ->label('Ekspor Data')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor data departemen selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->tooltip('Aksi Massal'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Departemen')
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
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
            'name',
            'description',
        ];
    }
}
