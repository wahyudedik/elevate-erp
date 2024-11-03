<?php

namespace App\Filament\Resources\CustomerSupportResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementCRM\TicketResponse;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class TicketResponseRelationManager extends RelationManager
{
    protected static string $relationship = 'ticketResponse';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Response')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ticket_id')
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->icon('heroicon-o-plus'),
            ]);
    }
}
