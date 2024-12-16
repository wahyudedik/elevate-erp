<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Employee;
use App\Models\ManagementSDM\Leave;
use Illuminate\Support\Facades\Auth;
use App\Filament\Exports\LeaveExporter;
use App\Filament\Imports\LeaveImporter;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LeaveResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LeaveResource\RelationManagers;
use Filament\Tables\Actions\ExportBulkAction;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationLabel = 'Ijin/Keluar';

    protected static ?string $modelLabel = 'Ijin/Keluar';

    protected static ?string $pluralModelLabel = 'Ijin/Keluar';

    protected static ?string $cluster = Employee::class;

    protected static ?int $navigationSort = 6;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'leave';

    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?string $navigationIcon = 'pepicon-leave-circle-off';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Leave Details')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->label('Cabang')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pengguna')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->afterOrEqual('start_date'),
                        Forms\Components\RichEditor::make('reason')
                            ->label('Alasan')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Tertunda',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->label('Status')
                            ->required()
                            ->default('pending'),
                        Forms\Components\RichEditor::make('note')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->nullable(),
                    ])
                    ->columns(2),
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
                    ->alignCenter()
                    ->size('sm')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->size('sm')
                    ->weight('medium')
                    ->icon('heroicon-m-building-office'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->sortable()
                    ->searchable()
                    ->size('sm')
                    ->weight('medium')
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->icon('heroicon-m-calendar')
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date('d M Y')
                    ->icon('heroicon-m-calendar-days')
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->html()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable()
                    ->size('sm')
                    ->icon('heroicon-m-document-text')
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->icon(fn(string $state): string => match ($state) {
                        'pending' => 'heroicon-m-clock',
                        'approved' => 'heroicon-m-check-circle',
                        'rejected' => 'heroicon-m-x-circle',
                    })
                    ->size('sm'),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(50)
                    ->html()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->icon('heroicon-m-clipboard-document-list')
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->icon('heroicon-m-clock')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm')
                    ->icon('heroicon-m-arrow-path')
                    ->color('gray'),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Cabang'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus')->label('Buat Cuti Baru'),
                Tables\Actions\Action::make('download')
                    ->label('Download Surat')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $pdf = app('dompdf.wrapper')->loadView('pdf.leave-letter');

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, "surat-permohonan-cuti.pdf");
                    }),
                ActionGroup::make([
                    ExportAction::make()->exporter(LeaveExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->label('Ekspor')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor cuti selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(LeaveImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->label('Impor')
                        ->after(function () {
                            Notification::make()
                                ->title('Impor cuti selesai' . ' ' . now())
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
                    ExportBulkAction::make()->exporter(LeaveExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Ekspor cuti selesai' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Cuti Baru')
                    ->icon('heroicon-o-plus')
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
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
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
            'user_id',
            'start_date',
            'end_date',
            'reason',
            'status',
            'note',
        ];
    }
}
