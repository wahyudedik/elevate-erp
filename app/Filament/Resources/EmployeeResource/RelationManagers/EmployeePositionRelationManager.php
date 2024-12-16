<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Models\Branch;
use App\Models\Department;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementSDM\EmployeePosition;
use App\Models\Position;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EmployeePositionRelationManager extends RelationManager
{
    protected static string $relationship = 'employeePosition';

    protected static ?string $title = 'Posisi Karyawan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Posisi Karyawan')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->label('Cabang'),
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
                        Forms\Components\DatePicker::make('start_date')->required()
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('employee_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->searchable()
                    ->sortable()
                    ->label('Karyawan')
                    ->toggleable()
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('department')
                    ->searchable()
                    ->sortable()
                    ->label('Departemen')
                    ->toggleable()
                    ->icon('heroicon-m-building-office-2'),
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->sortable()
                    ->label('Posisi')
                    ->description(fn(EmployeePosition $record): string => $record->start_date ? $record->start_date->diffForHumans($record->end_date ?? now(), ['syntax' => true]) : '-')
                    ->toggleable()
                    ->icon('heroicon-m-briefcase'),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->label('Tanggal Mulai')
                    ->icon('heroicon-m-calendar'),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Tanggal Berakhir')
                    ->icon('heroicon-m-calendar'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dibuat Pada')
                    ->icon('heroicon-m-clock'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Terakhir Diubah')
                    ->icon('heroicon-m-arrow-path'),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    // Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('endPosition')
                        ->label('End Position')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\DatePicker::make('end_date')
                                ->label('End Date')
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
                                ->title('Position ended')
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
                // CreateAction::make()->icon('heroicon-o-plus')->label('Buat Posisi Baru'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make()
                //     ->icon('heroicon-o-plus')
                //     ->label('Buat Posisi Baru'),
            ]);
    }
}
