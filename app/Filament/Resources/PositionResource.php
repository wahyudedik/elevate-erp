<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Position;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\PositionExporter;
use App\Filament\Imports\PositionImporter;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\PositionResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PositionResource\RelationManagers;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportBulkAction;

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
                Forms\Components\Section::make('Position Details')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn(Builder $query) => $query->where('status', 'active'))
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\Select::make('department_id')
                            ->relationship(
                                'department',
                                'name',
                                fn(Builder $query, Forms\Get $get) =>
                                $query->where('branch_id', $get('branch_id'))
                            )
                            ->searchable()
                            ->preload()
                            ->disabled(fn(Forms\Get $get): bool => ! $get('branch_id')),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->toggleable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('department.name')
                    ->searchable()
                    ->icon('heroicon-o-building-office-2')
                    ->sortable()
                    ->toggleable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->icon('heroicon-o-briefcase')
                    ->sortable()
                    ->size('sm')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->html()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('sm'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('xs'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size('xs')
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->preload()
                    ->multiple()
                    ->label('Branch'),
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->preload()
                    ->multiple()
                    ->label('Department'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ReplicateAction::make()
                        ->excludeAttributes(['branch_id', 'department_id'])
                        ->label('Duplicate')
                        ->icon('heroicon-o-document-duplicate'),
                ])
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Jabatan Baru')
                    ->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()->exporter(PositionExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export Position completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(PositionImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Import Position completed' . ' ' . now())
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
                                ->label('Department')
                                ->relationship('department', 'name')
                                ->required(),
                        ])
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-building-office')
                        ->label('Update Department'),
                    ExportBulkAction::make()->exporter(PositionExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export Position completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Jabatan Baru')
                    ->icon('heroicon-o-plus'),
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
        ];
    }
}
