<?php

namespace App\Filament\Resources\PurchaseTransactionResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Tables\Actions\ViewAction;
use App\Models\ManagementStock\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class SupplierRelationManager extends RelationManager
{
    protected static string $relationship = 'supplier';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('supplier_name')
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_code')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fax')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('website')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tax_identification_number')
                    ->searchable()
                    ->toggleable()
                    ->label('Tax ID'),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->html()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'danger' => 'inactive',
                        'success' => 'active',
                    ]),
                Tables\Columns\TextColumn::make('credit_limit')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->label('Status')
                    ->placeholder('All Statuses'),
                Tables\Filters\SelectFilter::make('country')
                    ->options(function () {
                        return Supplier::distinct()->pluck('country', 'country')->toArray();
                    })
                    ->label('Country')
                    ->placeholder('All Countries')
                    ->searchable(),
                Tables\Filters\Filter::make('credit_limit')
                    ->form([
                        Forms\Components\TextInput::make('credit_limit_from')
                            ->numeric()
                            ->label('Minimum Credit Limit'),
                        Forms\Components\TextInput::make('credit_limit_to')
                            ->numeric()
                            ->label('Maximum Credit Limit'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['credit_limit_from'],
                                fn(Builder $query, $limit): Builder => $query->where('credit_limit', '>=', $limit),
                            )
                            ->when(
                                $data['credit_limit_to'],
                                fn(Builder $query, $limit): Builder => $query->where('credit_limit', '<=', $limit),
                            );
                    })->columns(2),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
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
                Tables\Filters\TernaryFilter::make('has_website')
                    ->label('Has Website')
                    ->placeholder('All')
                    ->trueLabel('With Website')
                    ->falseLabel('Without Website')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('website'),
                        false: fn(Builder $query) => $query->whereNull('website'),
                    ),
                Tables\Filters\TernaryFilter::make('has_tax_id')
                    ->label('Has Tax ID')
                    ->placeholder('All')
                    ->trueLabel('With Tax ID')
                    ->falseLabel('Without Tax ID')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('tax_identification_number'),
                        false: fn(Builder $query) => $query->whereNull('tax_identification_number'),
                    ),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make()->icon('heroicon-o-plus'),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
