<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ProjectMonitoringExporter;
use App\Filament\Imports\ProjectMonitoringImporter;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\ManagementProject\ProjectMonitoring;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProjectMonitoringResource\Pages;
use App\Filament\Resources\ProjectMonitoringResource\RelationManagers;
use App\Filament\Resources\ProjectMonitoringResource\RelationManagers\ProjectRelationManager;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Support\Facades\Auth;

class ProjectMonitoringResource extends Resource
{
    protected static ?string $model = ProjectMonitoring::class;

    protected static ?string $navigationLabel = 'Monitoring Proyek';

    protected static ?string $modelLabel = 'Monitoring Proyek';
    
    protected static ?string $pluralModelLabel = 'Monitoring Proyek';

    protected static ?int $navigationSort = 26;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'projectMonitoring';

    protected static ?string $navigationGroup = 'Manajemen Projek';

    protected static ?string $navigationIcon = 'carbon-container-runtime-monitor';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Project Monitoring')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\RichEditor::make('progress_report')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'on_track' => 'On Track',
                                'at_risk' => 'At Risk',
                                'delayed' => 'Delayed',
                            ])
                            ->required()
                            ->default('on_track'),
                        Forms\Components\TextInput::make('completion_percentage')
                            ->label('Completion Percentage')
                            ->numeric()
                            ->prefix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->required(),
                        Forms\Components\DatePicker::make('report_date')
                            ->required()
                            ->default(now()),
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
                    ->label('Branch')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable(),
                TextColumn::make('project.name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('progress_report')
                    ->limit(50)
                    ->toggleable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->html()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->color(fn(string $state): string => match ($state) {
                        'on_track' => 'success',
                        'at_risk' => 'warning',
                        'delayed' => 'danger',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('completion_percentage')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->suffix('%')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),
                TextColumn::make('report_date')
                    ->date()
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
                SelectFilter::make('project')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Project'),
                SelectFilter::make('status')
                    ->options([
                        'on_track' => 'On Track',
                        'at_risk' => 'At Risk',
                        'delayed' => 'Delayed',
                    ])
                    ->multiple()
                    ->label('Status'),
                Filter::make('completion_percentage')
                    ->form([
                        Forms\Components\TextInput::make('completion_percentage_from')
                            ->numeric()
                            ->label('Minimum Completion Percentage'),
                        Forms\Components\TextInput::make('completion_percentage_to')
                            ->numeric()
                            ->label('Maximum Completion Percentage'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['completion_percentage_from'],
                                fn(Builder $query, $value): Builder => $query->where('completion_percentage', '>=', $value),
                            )
                            ->when(
                                $data['completion_percentage_to'],
                                fn(Builder $query, $value): Builder => $query->where('completion_percentage', '<=', $value),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['completion_percentage_from'] ?? null) {
                            $indicators['completion_percentage_from'] = 'Completion percentage from: ' . $data['completion_percentage_from'] . '%';
                        }
                        if ($data['completion_percentage_to'] ?? null) {
                            $indicators['completion_percentage_to'] = 'Completion percentage to: ' . $data['completion_percentage_to'] . '%';
                        }
                        return $indicators;
                    })->columns(2),
                Filter::make('report_date')
                    ->form([
                        Forms\Components\DatePicker::make('report_date_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('report_date_to')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['report_date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('report_date', '>=', $date),
                            )
                            ->when(
                                $data['report_date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('report_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['report_date_from'] ?? null) {
                            $indicators['report_date_from'] = 'Report date from: ' . Carbon::parse($data['report_date_from'])->toFormattedDateString();
                        }
                        if ($data['report_date_to'] ?? null) {
                            $indicators['report_date_to'] = 'Report date to: ' . Carbon::parse($data['report_date_to'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'on_track' => 'On Track',
                                    'at_risk' => 'At Risk',
                                    'delayed' => 'Delayed',
                                ])
                                ->required(),
                            Forms\Components\Textarea::make('progress_report')
                                ->label('Progress Report')
                                ->required(),
                            Forms\Components\TextInput::make('completion_percentage')
                                ->label('Completion Percentage')
                                ->numeric()
                                ->suffix('%')
                                ->minValue(0)
                                ->maxValue(100)
                                ->required(),
                        ])
                        ->action(function (array $data, ProjectMonitoring $record): void {
                            $record->update($data);
                            Notification::make()
                                ->title('Project status updated successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('generateReport')
                        ->label('Generate Report')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->action(function (ProjectMonitoring $record): void {
                            $project = $record->project;
                            $url = route('project-monitoring.report', $record->id);
                            redirect()->away($url);

                            Notification::make()
                                ->title('Report generated successfully')
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()->exporter(ProjectMonitoringExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export Project Monitoring Completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(ProjectMonitoringImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Import Project Monitoring Completed' . ' ' . now())
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
                    Tables\Actions\BulkAction::make('updateStatusBulk')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'on_track' => 'On Track',
                                    'at_risk' => 'At Risk',
                                    'delayed' => 'Delayed',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update($data);
                            Notification::make()
                                ->title('Project statuses updated successfully')
                                ->success()
                                ->send();
                        }),
                    ExportBulkAction::make()->exporter(ProjectMonitoringExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export Project Monitoring Completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProjectRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectMonitorings::route('/'),
            'create' => Pages\CreateProjectMonitoring::route('/create'),
            'edit' => Pages\EditProjectMonitoring::route('/{record}/edit'),
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
            'project_id',
            'progress_report',
            'status',
            'completion_percentage',
            'report_date',
        ];
    }
}
