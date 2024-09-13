<?php

namespace App\Filament\Resources;

use App\Filament\Exports\AttendanceExporter;
use App\Filament\Imports\AttendanceImporter;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ManagementSDM\Attendance;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Filament\Resources\AttendanceResource\RelationManagers\EmployeeRelationManager;
use App\Filament\Resources\AttendanceResource\RelationManagers\AttendanceRelationManager;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Support\Facades\Auth;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationBadgeTooltip = 'Total Attendance';

    protected static ?string $navigationLabel = 'Attendance Records';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management SDM';

    protected static ?string $navigationParentItem = 'Attendance';

    protected static ?string $navigationIcon = 'iconpark-checkin-o';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('date')
                            ->date()
                            ->required(),
                        Forms\Components\TimePicker::make('check_in'),
                        Forms\Components\TimePicker::make('check_out'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'present' => 'Present',
                                'absent' => 'Absent',
                                'late' => 'Late',
                                'on_leave' => 'On Leave',
                            ])
                            ->required()
                            ->default('present'),
                        Forms\Components\Textarea::make('note')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn($record): string => $record?->updated_at ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columns(2)
                    ->collapsible(),

            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label('Employee'),
                Tables\Columns\TextColumn::make('date')
                    ->date('Y-m-d')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_in')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Check In'),
                Tables\Columns\TextColumn::make('check_out')
                    ->time('H:i')
                    ->toggleable()
                    ->label('Check Out'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'secondary' => 'on_leave',
                    ]),
                Tables\Columns\TextColumn::make('note')
                    ->limit(30)
                    ->toggleable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload()
                    ->label('Employee'),
                Tables\Filters\Filter::make('date')
                    ->label('Date')
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
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'on_leave' => 'On Leave',
                    ])
                    ->multiple()
                    ->label('Status'),
                Tables\Filters\Filter::make('check_in')
                    ->form([
                        Forms\Components\TimePicker::make('check_in_from')
                            ->label('Check In From'),
                        Forms\Components\TimePicker::make('check_in_until')
                            ->label('Check In Until'),
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
                            $indicators['check_in_from'] = 'Check in from ' . $data['check_in_from'];
                        }
                        if ($data['check_in_until'] ?? null) {
                            $indicators['check_in_until'] = 'Check in until ' . $data['check_in_until'];
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\Filter::make('check_out')
                    ->form([
                        Forms\Components\TimePicker::make('check_out_from')
                            ->label('Check Out From'),
                        Forms\Components\TimePicker::make('check_out_until')
                            ->label('Check Out Until'),
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
                            $indicators['check_out_from'] = 'Check out from ' . $data['check_out_from'];
                        }
                        if ($data['check_out_until'] ?? null) {
                            $indicators['check_out_until'] = 'Check out until ' . $data['check_out_until'];
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_note')
                    ->label('Has Note')
                    ->placeholder('All')
                    ->trueLabel('With Notes')
                    ->falseLabel('Without Notes')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('note'),
                        false: fn(Builder $query) => $query->whereNull('note'),
                    ),
            ])
            ->actions([
                tables\Actions\ActionGroup::make([
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
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
                                    'present' => 'Present',
                                    'late' => 'Late',
                                ])
                                ->default('present')
                                ->required(),
                            Forms\Components\Textarea::make('note')
                                ->rows(3),
                        ])
                        ->action(function (array $data, Attendance $record): void {
                            $record->update($data);
                            Notification::make()
                                ->title('Checked In Successfully')
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
                                ->rows(3),
                        ])
                        ->action(function (array $data, Attendance $record): void {
                            $record->update($data);
                            Notification::make()
                                ->title('Checked Out Successfully')
                                ->success()
                                ->send();
                        })
                        ->visible(fn(Attendance $record): bool => $record->check_in !== null && $record->check_out === null),
                ])
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(AttendanceExporter::class)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->after(function () {
                        Notification::make()
                            ->title('Attendances exported successfully.')
                            ->success()
                            ->sendToDatabase(Auth::user());
                    }),
                ImportAction::make()
                    ->importer(AttendanceImporter::class)
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->after(function () {
                        Notification::make()
                            ->title('Attendances imported successfully.')
                            ->success()
                            ->sendToDatabase(Auth::user());
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()
                    ->exporter(AttendanceExporter::class)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->after(function () {
                        Notification::make()
                            ->title('Attendances exported successfully.')
                            ->success()
                            ->sendToDatabase(Auth::user());
                    })
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
