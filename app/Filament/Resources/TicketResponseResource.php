<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Doctrine\DBAL\Query;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementCRM\TicketResponse;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\TicketResponseExporter;
use App\Filament\Imports\TicketResponseImporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TicketResponseResource\Pages;
use App\Filament\Resources\TicketResponseResource\RelationManagers;

class TicketResponseResource extends Resource
{
    protected static ?string $model = TicketResponse::class;

    protected static ?string $navigationBadgeTooltip = 'Total Ticket Response';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management CRM';

    protected static ?string $navigationParentItem = 'Customer Supports';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Created At')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn($context) => $context === 'edit'),
                Forms\Components\DateTimePicker::make('updated_at')
                    ->label('Updated At')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn($context) => $context === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->filters([
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
                ExportAction::make()->exporter(TicketResponseExporter::class),
                ImportAction::make()->importer(TicketResponseImporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()->exporter(TicketResponseExporter::class),
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
            'index' => Pages\ListTicketResponses::route('/'),
            'create' => Pages\CreateTicketResponse::route('/create'),
            'edit' => Pages\EditTicketResponse::route('/{record}/edit'),
        ];
    }
}
