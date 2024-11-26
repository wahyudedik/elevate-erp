<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Doctrine\DBAL\Query;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Clusters\CustomerSupport;
use App\Models\ManagementCRM\TicketResponse;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\TicketResponseExporter;
use App\Filament\Imports\TicketResponseImporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TicketResponseResource\Pages;
use App\Filament\Resources\TicketResponseResource\RelationManagers;
use App\Filament\Resources\TicketResponseResource\RelationManagers\CustomerSupportRelationManager;
use Filament\Tables\Actions\CreateAction;

class TicketResponseResource extends Resource
{
    protected static ?string $model = TicketResponse::class;

    protected static ?string $navigationLabel = 'Respons Tiket';

    protected static ?string $modelLabel = 'Respons Tiket';
    
    protected static ?string $pluralModelLabel = 'Respons Tiket';

    protected static ?string $cluster = CustomerSupport::class;

    protected static ?int $navigationSort = 21;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'ticketResponses';

    protected static ?string $navigationGroup = 'Customer Support';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Response')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('ticket_id')
                            ->relationship('customerSupport', 'subject', fn(Builder $query) => $query->where('status', 'open'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Ticket'),
                        Forms\Components\RichEditor::make('response')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name', fn(Builder $query) => $query->where('status', 'active'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Employee'),
                    ]),
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
                Tables\Columns\TextColumn::make('customerSupport.subject')
                    ->label('Ticket')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('response')
                    ->label('Response')
                    ->toggleable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable()
                    ->html(),
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Employee')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
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
                Tables\Filters\SelectFilter::make('ticket')
                    ->relationship('customerSupport', 'subject')
                    ->searchable()
                    ->preload()
                    ->label('Ticket'),
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload()
                    ->label('Employee'),
                Tables\Filters\TernaryFilter::make('has_response')
                    ->label('Has Response')
                    ->placeholder('All responses')
                    ->trueLabel('With response')
                    ->falseLabel('Without response')
                    ->queries(
                        true: fn($query) => $query->whereNotNull('response'),
                        false: fn($query) => $query->whereNull('response'),
                    ),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn($query) => $query->whereDate('created_at', '>=', $data['created_from'])
                            )
                            ->when(
                                $data['created_until'],
                                fn($query) => $query->whereDate('created_at', '<=', $data['created_until'])
                            );
                    })->columns(2)
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('edit respond')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('ticket_id')
                                ->relationship('customerSupport', 'subject')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Forms\Components\Textarea::make('response')
                                ->required()
                                ->maxLength(65535),
                            Forms\Components\Select::make('employee_id')
                                ->relationship('employee', 'first_name')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function (array $data, TicketResponse $record): void {
                            $record->fill($data)->save();
                            Notification::make()
                                ->title('Response added successfully')
                                ->success()
                                ->send();
                        })
                ])
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()->exporter(TicketResponseExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export ticket completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()->importer(TicketResponseImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->after(function () {
                            Notification::make()
                                ->title('Import ticket completed' . ' ' . now())
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
                    ExportBulkAction::make()->exporter(TicketResponseExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Export ticket completed' . ' ' . now())
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CustomerSupportRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTicketResponses::route('/'),
            'create' => Pages\CreateTicketResponse::route('/create'),
            'edit' => Pages\EditTicketResponse::route('/{record}/edit'),
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
            'ticket_id',  // ID tiket dukungan yang direspons
            'response',          // ID karyawan yang memberikan respons
            'employee_id',        // Isi dari respons
        ];
    }
}
