<?php

namespace App\Filament\Resources\ProcurementItemResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProcurementsRelationManager extends RelationManager
{
    protected static string $relationship = 'procurements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Procurement Details')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'supplier_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\Section::make('Supplier Information')
                                    ->schema([
                                        Forms\Components\Hidden::make('company_id')
                                            ->default(Filament::getTenant()->id),
                                        Forms\Components\Select::make('branch_id')
                                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                                            ->nullable()
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\TextInput::make('supplier_name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('supplier_code')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('contact_name')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('fax')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('website')
                                            ->url()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('tax_identification_number')
                                            ->maxLength(255),
                                        Forms\Components\RichEditor::make('address')
                                            ->maxLength(65535)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('city')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('state')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('postal_code')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('country')
                                            ->maxLength(255),
                                        Forms\Components\Select::make('status')
                                            ->options([
                                                'active' => 'Active',
                                                'inactive' => 'Inactive',
                                            ])
                                            ->required()
                                            ->default('active'),
                                        Forms\Components\TextInput::make('credit_limit')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->maxValue(9999999999999.99),
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
                            ])->columns(2),
                        Forms\Components\DatePicker::make('procurement_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('total_cost')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(9999999999999.99)
                            ->step(0.01),
                        Forms\Components\Select::make('status')
                            ->options([
                                'ordered' => 'Ordered',
                                'received' => 'Received',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('ordered'),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('supplier.supplier_name')
                    ->label('Supplier')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('procurement_date')
                    ->label('Procurement Date')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'ordered',
                        'success' => 'received',
                    ])
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'supplier_name')
                    ->searchable()
                    ->preload()
                    ->label('Supplier'),
                Tables\Filters\Filter::make('total_cost')
                    ->form([
                        Forms\Components\TextInput::make('total_cost_from')
                            ->numeric()
                            ->label('Minimum Total Cost'),
                        Forms\Components\TextInput::make('total_cost_to')
                            ->numeric()
                            ->label('Maximum Total Cost'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['total_cost_from'],
                                fn(Builder $query, $cost): Builder => $query->where('total_cost', '>=', $cost),
                            )
                            ->when(
                                $data['total_cost_to'],
                                fn(Builder $query, $cost): Builder => $query->where('total_cost', '<=', $cost),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['total_cost_from'] ?? null) {
                            $indicators['total_cost_from'] = 'Total cost from: ' . $data['total_cost_from'];
                        }
                        if ($data['total_cost_to'] ?? null) {
                            $indicators['total_cost_to'] = 'Total cost to: ' . $data['total_cost_to'];
                        }
                        return $indicators;
                    })->columns(2),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'ordered' => 'Ordered',
                        'received' => 'Received',
                        'cancelled' => 'Cancelled',
                    ])
                    ->label('Status'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })->columns(2),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
