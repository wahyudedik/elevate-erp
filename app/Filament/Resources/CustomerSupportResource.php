<?php

namespace App\Filament\Resources;

use Schema;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementCRM\CustomerSupport;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\CustomerSupportExporter;
use App\Filament\Imports\CustomerSupportImporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CustomerSupportResource\Pages;
use App\Filament\Resources\CustomerSupportResource\RelationManagers;
use App\Filament\Clusters\CustomerSupport as ClustersCustomerSupport;
use App\Filament\Resources\CustomerSupportResource\RelationManagers\TicketResponseRelationManager;

class CustomerSupportResource extends Resource
{
    protected static ?string $model = CustomerSupport::class;

    protected static ?string $cluster = ClustersCustomerSupport::class;

    protected static ?int $navigationSort = 20;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'customerSupport';

    protected static ?string $navigationGroup = 'Customer Support';

    protected static ?string $navigationIcon = 'hugeicons-customer-service';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Customer Support Details')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name', fn(Builder $query) => $query->where('status', 'active'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4),
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->default('open')
                            ->required(),
                        Forms\Components\TextInput::make('customer_rating')
                            ->numeric()
                            ->minValue(1.0)
                            ->maxValue(5.0)
                            ->step(0.1)
                            ->nullable(),
                        Forms\Components\Select::make('customer_satisfaction')
                            ->options([
                                'satisfied' => 'Satisfied',
                                'neutral' => 'Neutral',
                                'dissatisfied' => 'Dissatisfied',
                            ])
                            ->nullable(),
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
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->limit(30)
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->toggleable()
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('priority')
                    ->toggleable()
                    ->badge()
                    ->colors([
                        'danger' => 'high',
                        'warning' => 'medium',
                        'success' => 'low',
                    ]),
                Tables\Columns\TextColumn::make('status')
                    ->toggleable()
                    ->badge()
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'in_progress',
                        'success' => 'resolved',
                        'secondary' => 'closed',
                    ]),
                Tables\Columns\TextColumn::make('customer_rating')
                    ->toggleable()
                    ->label('Rating')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 1) . '/5.0' : '-'),
                Tables\Columns\TextColumn::make('customer_satisfaction')
                    ->toggleable()
                    ->badge()
                    ->colors([
                        'success' => 'satisfied',
                        'warning' => 'neutral',
                        'danger' => 'dissatisfied',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
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
                    })->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('change_status')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'open' => 'Open',
                                    'in_progress' => 'In Progress',
                                    'resolved' => 'Resolved',
                                    'closed' => 'Closed',
                                ])
                                ->required(),
                        ])
                        ->action(function (CustomerSupport $record, array $data): void {
                            $record->update(['status' => $data['status']]);
                            Notification::make()
                                ->title('Status updated successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('change_priority')
                        ->label('Change Priority')
                        ->icon('heroicon-o-flag')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('priority')
                                ->options([
                                    'low' => 'Low',
                                    'medium' => 'Medium',
                                    'high' => 'High',
                                ])
                                ->required(),
                        ])
                        ->action(function (CustomerSupport $record, array $data): void {
                            $record->update(['priority' => $data['priority']]);
                            Notification::make()
                                ->title('Priority updated successfully')
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()->exporter(CustomerSupportExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export ticket list completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(CustomerSupportImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Import ticket list completed' . ' ' . now())
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
                    ExportBulkAction::make()->exporter(CustomerSupportExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export ticket list completed' . ' ' . now())
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
            TicketResponseRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerSupports::route('/'),
            'create' => Pages\CreateCustomerSupport::route('/create'),
            'edit' => Pages\EditCustomerSupport::route('/{record}/edit'),
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
            'customer_id',
            'subject',
            'description',
            'status',  // open, in_progress, resolved, closed
            'priority',  // low, medium, high, urgent
            'customer_rating',  // Rating from 1.0 to 5.0
            'customer_satisfaction',  // satisfied, neutral, dissatisfied
        ];
    }
}
