<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Position;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Employee;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementSDM\EmployeePosition;
use App\Filament\Exports\EmployeePositionExporter;
use App\Filament\Imports\EmployeePositionImporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmployeePositionResource\Pages;
use App\Filament\Resources\EmployeePositionResource\RelationManagers;
use App\Filament\Resources\EmployeePositionResource\RelationManagers\EmployeeRelationManager;

class EmployeePositionResource extends Resource
{
    protected static ?string $model = EmployeePosition::class;

    protected static ?string $navigationLabel = 'Jabatan Karyawan';

    protected static ?string $modelLabel = 'Jabatan Karyawan';

    protected static ?string $pluralModelLabel = 'Jabatan Karyawan';

    protected static ?string $cluster = Employee::class;

    protected static ?int $navigationSort = 2; //29

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'employeePosition';

    protected static ?string $navigationGroup = 'Employee Management';

    protected static ?string $navigationIcon = 'clarity-employee-line';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Jabatan Karyawan')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->label('Cabang'),
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Karyawan'),
                        Forms\Components\Select::make('department')
                            ->options(fn($get) => Department::query()
                                ->where('branch_id', $get('branch_id'))
                                ->pluck('name', 'name')
                                ->toArray())
                            ->required()
                            ->searchable()
                            ->live()
                            ->label('Departemen'),
                        Forms\Components\Select::make('position')
                            ->options(fn($get) => Position::query()
                                ->where('branch_id', $get('branch_id'))
                                ->pluck('name', 'name')
                                ->toArray())
                            ->required()
                            ->searchable()
                            ->live()
                            ->label('Posisi'),
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->default(now())
                            ->label('Tanggal Mulai'),
                        Forms\Components\DatePicker::make('end_date')
                            ->nullable()
                            ->label('Tanggal Berakhir'),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->searchable()
                    ->sortable()
                    ->label('Karyawan')
                    ->icon('heroicon-m-user')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable()
                    ->sortable()
                    ->label('Departemen')
                    ->icon('heroicon-m-building-office-2')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->sortable()
                    ->label('Posisi')
                    ->icon('heroicon-m-briefcase')
                    ->description(fn(EmployeePosition $record): string => $record->start_date ? $record->start_date->diffForHumans($record->end_date ?? now(), ['syntax' => true]) : '-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->label('Tanggal Mulai')
                    ->icon('heroicon-m-calendar')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->label('Tanggal Berakhir')
                    ->icon('heroicon-m-calendar')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Dibuat Pada')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Terakhir Diubah')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('Filter berdasarkan Karyawan'),
                Tables\Filters\Filter::make('active')
                    ->query(fn(Builder $query): Builder => $query->whereNull('end_date'))
                    ->toggle()
                    ->label('Tampilkan Hanya Posisi Aktif'),
                Tables\Filters\Filter::make('start_date')
                    ->form([
                        DatePicker::make('start'),
                        DatePicker::make('end')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['end'],
                                fn(Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    })->columns(2),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('endPosition')
                        ->label('Akhiri Posisi')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\DatePicker::make('end_date')
                                ->label('Tanggal Berakhir')
                                ->required()
                                ->minDate(fn(EmployeePosition $record): string => $record->start_date)
                                ->default(now()),
                        ])
                        ->action(function (EmployeePosition $record, array $data): void {
                            $record->update([
                                'end_date' => $data['end_date'],
                            ]);
                            $record->employee->update([
                                'position_id' => null,
                            ]);
                            Notification::make()
                                ->title('Posisi telah diakhiri')
                                ->success()
                                ->send();
                        })
                        ->hidden(fn(EmployeePosition $record): bool => $record->end_date !== null),
                    Tables\Actions\Action::make('extendPosition')
                        ->label('Extend Position')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->form([
                            Forms\Components\DatePicker::make('new_end_date')
                                ->label('New End Date')
                                ->required()
                                ->minDate(fn(EmployeePosition $record): string => $record->end_date ?? $record->start_date),
                        ])
                        ->action(function (EmployeePosition $record, array $data): void {
                            $record->update([
                                'end_date' => $data['new_end_date'],
                            ]);
                            Notification::make()
                                ->title('Position extended')
                                ->success()
                                ->send();
                        })
                        ->hidden(fn(EmployeePosition $record): bool => $record->end_date === null),
                ]),
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus')->label('Buat Posisi Baru'),
                ActionGroup::make([
                    ExportAction::make()->exporter(EmployeePositionExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor Posisi selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(EmployeePositionImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Impor Posisi selesai' . ' ' . now())
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
                    ExportBulkAction::make()->exporter(EmployeePositionExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor Posisi selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')->label('Buat Posisi Baru'),
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
            'index' => Pages\ListEmployeePositions::route('/'),
            'create' => Pages\CreateEmployeePosition::route('/create'),
            'edit' => Pages\EditEmployeePosition::route('/{record}/edit'),
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
            'employee_id',
            'department',
            'position',
            'start_date',
            'end_date',
        ];
    }
}
