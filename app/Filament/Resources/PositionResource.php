<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Position;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\PositionExporter;
use App\Filament\Imports\PositionImporter;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\PositionResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PositionResource\RelationManagers;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static ?string $navigationLabel = 'Jabatan';

    protected static ?string $modelLabel = 'Jabatan';

    protected static ?string $pluralModelLabel = 'Jabatan';

    protected static ?int $navigationSort = 6;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'positions';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jabatan')
                    ->description('Silakan isi informasi jabatan dengan lengkap')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name', fn(Builder $query) => $query->where('status', 'active'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->native(false)
                            ->helperText('Pilih cabang tempat jabatan ini berada'),
                        Forms\Components\Select::make('department_id')
                            ->label('Departemen')
                            ->relationship(
                                'department',
                                'name',
                                fn(Builder $query, Forms\Get $get) =>
                                $query->where('branch_id', $get('branch_id'))
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->hidden(fn(Forms\Get $get): bool => ! $get('branch_id'))
                            ->helperText('Pilih departemen untuk jabatan ini'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Jabatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama jabatan')
                            ->helperText('Contoh: Manager, Supervisor, Staff'),
                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Tuliskan deskripsi atau keterangan tambahan tentang jabatan ini')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->description('Detail waktu pembuatan dan modifikasi data')
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
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->toggleable()
                    ->size('sm')
                    ->weight('medium')
                    ->tooltip('Lokasi Cabang')
                    ->copyable()
                    ->copyMessage('Nama cabang disalin')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departemen')
                    ->searchable()
                    ->icon('heroicon-o-building-office-2')
                    ->sortable()
                    ->toggleable()
                    ->size('sm')
                    ->weight('medium')
                    ->tooltip('Nama Departemen')
                    ->copyable()
                    ->copyMessage('Nama departemen disalin')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jabatan')
                    ->searchable()
                    ->icon('heroicon-o-briefcase')
                    ->sortable()
                    ->size('sm')
                    ->weight('medium')
                    ->tooltip('Nama Posisi/Jabatan')
                    ->copyable()
                    ->copyMessage('Nama jabatan disalin')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->html()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->tooltip('Deskripsi Jabatan'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('xs'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('xs')
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Status Arsip'),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->preload()
                    ->multiple()
                    ->searchable()
                    ->label('Cabang'),
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->preload()
                    ->multiple()
                    ->searchable()
                    ->label('Departemen'),
                Tables\Filters\Filter::make('created')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dibuat Dari'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Dibuat Sampai'),
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
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Ubah')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->icon('heroicon-o-eye')
                        ->color('info'),
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
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Data')
                    ->icon('heroicon-m-plus')
                    ->color('primary'),
                ActionGroup::make([
                    ExportAction::make()->exporter(PositionExporter::class)
                        ->label('Ekspor Data')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor data jabatan selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(PositionImporter::class)
                        ->label('Impor Data')
                        ->icon('heroicon-m-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Impor data jabatan selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->icon('heroicon-m-cog-6-tooth')
                    ->label('Lainnya')
                    ->tooltip('Opsi Tambahan')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success'),
                    Tables\Actions\BulkAction::make('updateDepartment')
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'department_id' => $data['department_id'],
                                ]);
                            }
                        })
                        ->form([
                            Forms\Components\Select::make('department_id')
                                ->label('Departemen')
                                ->relationship('department', 'name')
                                ->required(),
                        ])
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-building-office')
                        ->label('Perbarui Departemen')
                        ->color('warning'),
                    ExportBulkAction::make()->exporter(PositionExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->label('Ekspor Data')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor data jabatan selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])->tooltip('Aksi Massal'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Jabatan')
                    ->icon('heroicon-o-plus')
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
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
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
            'name',
            'description',
            'company_id',
            'branch_id',
            'department_id',
        ];
    }
}
